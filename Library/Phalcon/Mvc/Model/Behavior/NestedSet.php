<?php

namespace Phalcon\Mvc\Model\Behavior;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Db\AdapterInterface;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\Model\ResultsetInterface;

class NestedSet extends Behavior implements BehaviorInterface
{
    /**
     * @var AdapterInterface|null
     */
    private $db;

    /**
     * @var ModelInterface|null
     */
    private $owner;

    private $hasManyRoots = false;
    private $rootAttribute = 'root';
    private $leftAttribute = 'lft';
    private $rightAttribute = 'rgt';
    private $levelAttribute = 'level';
    private $primaryKey = 'id';
    private $ignoreEvent = false;
    private $deleted = false;

    public function __construct($options = null)
    {
        if (isset($options['db']) && $options['db'] instanceof AdapterInterface) {
            $this->db = $options['db'];
        }

        if (isset($options['hasManyRoots'])) {
            $this->hasManyRoots = (bool) $options['hasManyRoots'];
        }

        if (isset($options['rootAttribute'])) {
            $this->rootAttribute = $options['rootAttribute'];
        }

        if (isset($options['leftAttribute'])) {
            $this->leftAttribute = $options['leftAttribute'];
        }

        if (isset($options['rightAttribute'])) {
            $this->rightAttribute = $options['rightAttribute'];
        }

        if (isset($options['levelAttribute'])) {
            $this->levelAttribute = $options['levelAttribute'];
        }

        if (isset($options['primaryKey'])) {
            $this->primaryKey = $options['primaryKey'];
        }
    }

    /**
     * @param string $eventType
     * @param ModelInterface $model
     * @throws Exception
     */
    public function notify($eventType, ModelInterface $model)
    {
        switch ($eventType) {
            case 'beforeCreate':
            case 'beforeDelete':
            case 'beforeUpdate':
                if (!$this->ignoreEvent) {
                    throw new Exception(
                        sprintf(
                            'You should not use %s:%s when %s attached. Use the methods of behavior.',
                            get_class($model),
                            $eventType,
                            __CLASS__
                        )
                    );
                }
                break;
        }
    }

    /**
     * Calls a method when it's missing in the model
     *
     * @param ModelInterface $model
     * @param string $method
     * @param null $arguments
     * @return mixed|null|string
     * @throws Exception
     */
    public function missingMethod(ModelInterface $model, $method, $arguments = null)
    {
        if (!method_exists($this, $method)) {
            return null;
        }

        $this->getDbHandler($model);
        $this->setOwner($model);

        return call_user_func_array([$this, $method], $arguments);
    }

    /**
     * @return ModelInterface
     */
    public function getOwner()
    {
        if (!$this->owner instanceof ModelInterface) {
            trigger_error("Owner isn't a valid ModelInterface instance.", E_USER_WARNING);
        }

        return $this->owner;
    }

    public function setOwner(ModelInterface $owner)
    {
        $this->owner = $owner;
    }

    public function getIsNewRecord()
    {
        return $this->getOwner()->getDirtyState() == Model::DIRTY_STATE_TRANSIENT;
    }

    /**
     * Returns if the current node is deleted.
     *
     * @return boolean whether the node is deleted.
     */
    public function getIsDeletedRecord()
    {
        return $this->deleted;
    }

    /**
     * Sets if the current node is deleted.
     *
     * @param boolean $value whether the node is deleted.
     */
    public function setIsDeletedRecord($value)
    {
        $this->deleted = $value;
    }

    /**
     * Determines if node is leaf.
     *
     * @return boolean whether the node is leaf.
     */
    public function isLeaf()
    {
        $owner = $this->getOwner();

        return $owner->{$this->rightAttribute} - $owner->{$this->leftAttribute} === 1;
    }

    /**
     * Determines if node is root.
     *
     * @return boolean whether the node is root.
     */
    public function isRoot()
    {
        return $this->getOwner()->{$this->leftAttribute} == 1;
    }

    /**
     * Determines if node is descendant of subject node.
     *
     * @param  \Phalcon\Mvc\ModelInterface $subj the subject node.
     *
     * @return boolean                     whether the node is descendant of subject node.
     */
    public function isDescendantOf($subj)
    {
        $owner = $this->getOwner();
        $result = ($owner->{$this->leftAttribute} > $subj->{$this->leftAttribute})
            && ($owner->{$this->rightAttribute} < $subj->{$this->rightAttribute});

        if ($this->hasManyRoots) {
            $result = $result && ($owner->{$this->rootAttribute} === $subj->{$this->rootAttribute});
        }

        return $result;
    }

    /**
     * Named scope. Gets descendants for node.
     *
     * @param int $depth the depth.
     * @param boolean $addSelf If TRUE - parent node will be added to result set.
     * @return ResultsetInterface
     */
    public function descendants($depth = null, $addSelf = false)
    {
        $owner = $this->getOwner();

        $query = $owner::query()
            ->where($this->leftAttribute . '>' . ($addSelf ? '=' : null) . $owner->{$this->leftAttribute})
            ->andWhere($this->rightAttribute . '<' . ($addSelf ? '=' : null) . $owner->{$this->rightAttribute})
            ->orderBy($this->leftAttribute);

        if ($depth !== null) {
            $query = $query->andWhere($this->levelAttribute . '<=' . ($owner->{$this->levelAttribute} + $depth));
        }

        if ($this->hasManyRoots) {
            $query = $query->andWhere($this->rootAttribute . '=' . $owner->{$this->rootAttribute});
        }

        return $query->execute();
    }

    /**
     * Named scope. Gets children for node (direct descendants only).
     *
     * @return ResultsetInterface
     */
    public function children()
    {
        return $this->descendants(1);
    }

    /**
     * Named scope. Gets ancestors for node.
     *
     * @param  int $depth the depth.
     * @return ResultsetInterface
     */
    public function ancestors($depth = null)
    {
        $owner = $this->getOwner();

        $query = $owner::query()
            ->where($this->leftAttribute . '<' . $owner->{$this->leftAttribute})
            ->andWhere($this->rightAttribute . '>' . $owner->{$this->rightAttribute})
            ->orderBy($this->leftAttribute);

        if ($depth !== null) {
            $query = $query->andWhere($this->levelAttribute . '>=' . ($owner->{$this->levelAttribute} - $depth));
        }

        if ($this->hasManyRoots) {
            $query = $query->andWhere($this->rootAttribute . '=' . $owner->{$this->rootAttribute});
        }

        return $query->execute();
    }

    /**
     * Named scope. Gets root node(s).
     *
     * @return ResultsetInterface
     */
    public function roots()
    {
        $owner = $this->getOwner();

        return $owner::find($this->leftAttribute . ' = 1');
    }

    /**
     * Named scope. Gets parent of node.
     *
     * @return \Phalcon\Mvc\ModelInterface
     */
    public function parent()
    {
        $owner = $this->getOwner();

        $query = $owner::query()
            ->where($this->leftAttribute . '<' . $owner->{$this->leftAttribute})
            ->andWhere($this->rightAttribute . '>' . $owner->{$this->rightAttribute})
            ->orderBy($this->rightAttribute)
            ->limit(1);

        if ($this->hasManyRoots) {
            $query = $query->andWhere($this->rootAttribute . '=' . $owner->{$this->rootAttribute});
        }

        return $query->execute()->getFirst();
    }

    /**
     * Named scope. Gets previous sibling of node.
     *
     * @return ModelInterface
     */
    public function prev()
    {
        $owner = $this->getOwner();
        $query = $owner::query()
            ->where($this->rightAttribute . '=' . ($owner->{$this->leftAttribute} - 1));

        if ($this->hasManyRoots) {
            $query = $query->andWhere($this->rootAttribute . '=' . $owner->{$this->rootAttribute});
        }

        return $query->execute()->getFirst();
    }

    /**
     * Named scope. Gets next sibling of node.
     *
     * @return ModelInterface
     */
    public function next()
    {
        $owner = $this->getOwner();
        $query = $owner::query()
            ->where($this->leftAttribute . '=' . ($owner->{$this->rightAttribute} + 1));

        if ($this->hasManyRoots) {
            $query = $query->andWhere($this->rootAttribute . '=' . $owner->{$this->rootAttribute});
        }

        return $query->execute()->getFirst();
    }

    /**
     * Prepends node to target as first child.
     *
     * @param  ModelInterface $target the target
     * @param  array $attributes List of attributes.
     * @return boolean
     */
    public function prependTo(ModelInterface $target, array $attributes = null)
    {
        // Re-search $target
        $target = $target::findFirst($target->{$this->primaryKey});

        return $this->addNode($target, $target->{$this->leftAttribute} + 1, 1, $attributes);
    }

    /**
     * Prepends target to node as first child.
     *
     * @param  ModelInterface $target the target.
     * @param  array $attributes list of attributes.
     * @return boolean
     */
    public function prepend(ModelInterface $target, array $attributes = null)
    {
        return $target->prependTo($this->getOwner(), $attributes);
    }

    /**
     * Appends node to target as last child.
     *
     * @param  ModelInterface $target the target.
     * @param  array $attributes list of attributes.
     * @return boolean
     */
    public function appendTo(ModelInterface $target, array $attributes = null)
    {
        // Re-search $target
        $target = $target::findFirst($target->{$this->primaryKey});

        return $this->addNode($target, $target->{$this->rightAttribute}, 1, $attributes);
    }

    /**
     * Appends target to node as last child.
     *
     * @param  ModelInterface $target the target.
     * @param  array $attributes list of attributes.
     * @return boolean
     */
    public function append(ModelInterface $target, array $attributes = null)
    {
        return $target->appendTo($this->getOwner(), $attributes);
    }

    /**
     * Inserts node as previous sibling of target.
     *
     * @param ModelInterface $target the target.
     * @param  array $attributes list of attributes.
     * @return boolean
     */
    public function insertBefore(ModelInterface $target, array $attributes = null)
    {
        return $this->addNode($target, $target->{$this->leftAttribute}, 0, $attributes);
    }

    /**
     * Inserts node as next sibling of target.
     *
     * @param  ModelInterface $target the target.
     * @param  array $attributes list of attributes.
     * @return boolean
     */
    public function insertAfter(ModelInterface $target, array $attributes = null)
    {
        return $this->addNode($target, $target->{$this->rightAttribute} + 1, 0, $attributes);
    }

    /**
     * Move node as previous sibling of target.
     *
     * @param  ModelInterface $target the target.
     * @return boolean
     */
    public function moveBefore(ModelInterface $target)
    {
        return $this->moveNode($target, $target->{$this->leftAttribute}, 0);
    }

    /**
     * Move node as next sibling of target.
     *
     * @param  ModelInterface $target the target.
     * @return boolean
     */
    public function moveAfter(ModelInterface $target)
    {
        return $this->moveNode($target, $target->{$this->rightAttribute} + 1, 0);
    }

    /**
     * Move node as first child of target.
     *
     * @param  ModelInterface $target the target.
     * @return boolean
     */
    public function moveAsFirst(ModelInterface $target)
    {
        return $this->moveNode($target, $target->{$this->leftAttribute} + 1, 1);
    }

    /**
     * Move node as last child of target.
     *
     * @param  ModelInterface $target the target.
     * @return boolean
     */
    public function moveAsLast(ModelInterface $target)
    {
        return $this->moveNode($target, $target->{$this->rightAttribute}, 1);
    }

    /**
     * Move node as new root.
     *
     * @return boolean
     * @throws Exception
     */
    public function moveAsRoot()
    {
        $owner = $this->getOwner();

        if (!$this->hasManyRoots) {
            throw new Exception('Many roots mode is off.');
        }

        if ($this->getIsNewRecord()) {
            throw new Exception('The node should not be new record.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new Exception('The node should not be deleted.');
        }

        if ($owner->isRoot()) {
            throw new Exception('The node already is root node.');
        }

        $this->db->begin();

        $left = $owner->{$this->leftAttribute};
        $right = $owner->{$this->rightAttribute};
        $levelDelta = 1 - $owner->{$this->levelAttribute};
        $delta = 1 - $left;

        $condition = $this->leftAttribute . '>=' . $left . ' AND ';
        $condition .= $this->rightAttribute . '<=' . $right . ' AND ';
        $condition .= $this->rootAttribute . '=' . $owner->{$this->rootAttribute};

        $this->ignoreEvent = true;
        foreach ($owner::find($condition) as $i) {
            $arr = array(
                $this->leftAttribute => $i->{$this->leftAttribute} + $delta,
                $this->rightAttribute => $i->{$this->rightAttribute} + $delta,
                $this->levelAttribute => $i->{$this->levelAttribute} + $levelDelta,
                $this->rootAttribute => $owner->{$this->primaryKey}
            );
            if ($i->update($arr) == false) {
                $this->db->rollback();
                $this->ignoreEvent = false;

                return false;
            }
        }
        $this->ignoreEvent = false;

        $this->shiftLeftRight($right + 1, $left - $right - 1);

        $this->db->commit();

        return true;
    }

    /**
     * Create root node if multiple-root tree mode. Update node if it's not new.
     *
     * @param  array $attributes list of attributes.
     * @param  array $whiteList  whether to perform validation.
     * @return boolean
     */
    public function saveNode(array $attributes = null, array $whiteList = null)
    {
        $owner = $this->getOwner();

        $this->ignoreEvent = true;

        if (!$owner->readAttribute($this->primaryKey)) {
            $result = $this->makeRoot($attributes, $whiteList);
        } else {
            $result = $owner->update($attributes, $whiteList);
        }

        $this->ignoreEvent = false;

        return $result;
    }

    /**
     * Deletes node and it's descendants.
     *
     * @return boolean
     * @throws Exception
     */
    public function deleteNode()
    {
        $owner = $this->getOwner();

        if ($this->getIsNewRecord()) {
            throw new Exception('The node cannot be deleted because it is new.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new Exception('The node cannot be deleted because it is already deleted.');
        }

        $this->db->begin();

        if ($owner->isLeaf()) {
            $this->ignoreEvent = true;
            if ($owner->delete() == false) {
                $this->db->rollback();
                $this->ignoreEvent = false;

                return false;
            }
            $this->ignoreEvent = false;
        } else {
            $condition = $this->leftAttribute . '>=' . $owner->{$this->leftAttribute} . ' AND ';
            $condition .= $this->rightAttribute . '<=' . $owner->{$this->rightAttribute};

            if ($this->hasManyRoots) {
                $condition .= ' AND ' . $this->rootAttribute . '=' . $owner->{$this->rootAttribute};
            }

            $this->ignoreEvent = true;
            foreach ($owner::find($condition) as $i) {
                if ($i->delete() == false) {
                    $this->db->rollback();
                    $this->ignoreEvent = false;

                    return false;
                }
            }
            $this->ignoreEvent = false;
        }

        $key = $owner->{$this->rightAttribute} + 1;
        $delta = $owner->{$this->leftAttribute} - $owner->{$this->rightAttribute} - 1;
        $this->shiftLeftRight($key, $delta);

        $this->db->commit();

        return true;
    }

    /**
     * Gets DB handler.
     *
     * @param ModelInterface $model
     * @return AdapterInterface
     * @throws Exception
     */
    private function getDbHandler(ModelInterface $model)
    {
        if (!$this->db instanceof AdapterInterface) {
            if ($model->getDi()->has('db')) {
                $db = $model->getDi()->getShared('db');
                if (!$db instanceof AdapterInterface) {
                    throw new Exception('The "db" service which was obtained from DI is invalid adapter.');
                }
                $this->db = $db;
            } else {
                throw new Exception('Undefined database handler.');
            }
        }

        return $this->db;
    }

    /**
     * @param  ModelInterface $target
     * @param  int $key
     * @param  int $levelUp
     *
     * @return boolean
     * @throws Exception
     */
    private function moveNode(ModelInterface $target, $key, $levelUp)
    {
        $owner = $this->getOwner();

        if ($this->getIsNewRecord()) {
            throw new Exception('The node should not be new record.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new Exception('The node should not be deleted.');
        }

        if ($target->getIsDeletedRecord()) {
            throw new Exception('The target node should not be deleted.');
        }

        if ($owner == $target) {
            throw new Exception('The target node should not be self.');
        }

        if ($target->isDescendantOf($owner)) {
            throw new Exception('The target node should not be descendant.');
        }

        if (!$levelUp && $target->isRoot()) {
            throw new Exception('The target node should not be root.');
        }

        $this->db->begin();

        $left = $owner->{$this->leftAttribute};
        $right = $owner->{$this->rightAttribute};
        $levelDelta = $target->{$this->levelAttribute} - $owner->{$this->levelAttribute} + $levelUp;

        if ($this->hasManyRoots && $owner->{$this->rootAttribute} !== $target->{$this->rootAttribute}) {
            $this->ignoreEvent = true;

            // 1. Rebuild the target tree
            foreach ([$this->leftAttribute, $this->rightAttribute] as $attribute) {
                $condition = join(' AND ', [
                    $attribute . '>=' . $key,
                    $this->rootAttribute . '=' . $target->{$this->rootAttribute},
                ]);
                foreach ($target::find($condition) as $i) {
                    $delta = $right - $left + 1;
                    /** @var ModelInterface $i */
                    if (!$i->update([$attribute => $i->{$attribute} + $delta])) {
                        $this->db->rollback();
                        $this->ignoreEvent = false;

                        return false;
                    }
                }
            }

            $delta = $key - $left;

            // 2. Rebuild the owner's tree of children (owner sub-tree)
            $condition = $this->leftAttribute . '>=' . $left . ' AND ';
            $condition .= $this->rightAttribute . '<=' . $right . ' AND ';
            $condition .= $this->rootAttribute . '=' . $owner->{$this->rootAttribute};

            foreach ($owner::find($condition) as $i) {
                $arr = [
                    $this->leftAttribute => $i->{$this->leftAttribute} + $delta,
                    $this->rightAttribute => $i->{$this->rightAttribute} + $delta,
                    $this->levelAttribute => $i->{$this->levelAttribute} + $levelDelta,
                    $this->rootAttribute => $target->{$this->rootAttribute}
                ];

                if ($i->update($arr) == false) {
                    $this->db->rollback();
                    $this->ignoreEvent = false;

                    return false;
                }
            }

            // 3. Rebuild the owner tree
            $this->shiftLeftRight($right + 1, $left - $right - 1, $owner);

            $this->ignoreEvent = false;
            $this->db->commit();
        } else {
            $delta = $right - $left + 1;
            $this->ignoreEvent = true;
            $this->shiftLeftRight($key, $delta);

            if ($left >= $key) {
                $left += $delta;
                $right += $delta;
            }

            $condition = $this->leftAttribute . '>=' . $left . ' AND ' . $this->rightAttribute . '<=' . $right;

            if ($this->hasManyRoots) {
                $condition .= ' AND ' . $this->rootAttribute . '=' . $owner->{$this->rootAttribute};
            }

            foreach ($owner::find($condition) as $i) {
                if ($i->update(array($this->levelAttribute => $i->{$this->levelAttribute} + $levelDelta)) == false) {
                    $this->db->rollback();
                    $this->ignoreEvent = false;

                    return false;
                }
            }

            foreach (array($this->leftAttribute, $this->rightAttribute) as $attribute) {
                $condition = $attribute . '>=' . $left . ' AND ' . $attribute . '<=' . $right;

                if ($this->hasManyRoots) {
                    $condition .= ' AND ' . $this->rootAttribute . '=' . $owner->{$this->rootAttribute};
                }

                foreach ($owner::find($condition) as $i) {
                    if ($i->update(array($attribute => $i->{$attribute} + $key - $left)) == false) {
                        $this->db->rollback();
                        $this->ignoreEvent = false;

                        return false;
                    }
                }
            }

            $this->shiftLeftRight($right + 1, -$delta);
            $this->ignoreEvent = false;

            $this->ignoreEvent = false;
            $this->db->commit();
        }

        return true;
    }

    /**
     * @param int $key
     * @param int $delta
     * @param ModelInterface $model
     * @return boolean
     */
    private function shiftLeftRight($key, $delta, ModelInterface $model = null)
    {
        $owner = $model ?: $this->getOwner();

        foreach ([$this->leftAttribute, $this->rightAttribute] as $attribute) {
            $condition = $attribute . '>=' . $key;

            if ($this->hasManyRoots) {
                $condition .= ' AND ' . $this->rootAttribute . '=' . $owner->{$this->rootAttribute};
            }

            foreach ($owner::find($condition) as $i) {
                /** @var ModelInterface $i */
                if ($i->update([$attribute => $i->{$attribute} + $delta]) == false) {
                    $this->db->rollback();
                    $this->ignoreEvent = false;

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param  ModelInterface $target
     * @param  int $key
     * @param  int $levelUp
     * @param  array $attributes
     *
     * @return boolean
     * @throws \Exception
     */
    private function addNode(ModelInterface $target, $key, $levelUp, array $attributes = null)
    {
        $owner = $this->getOwner();

        if (!$this->getIsNewRecord()) {
            throw new Exception('The node cannot be inserted because it is not new.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new Exception('The node cannot be inserted because it is deleted.');
        }

        if ($target->getIsDeletedRecord()) {
            throw new Exception('The node cannot be inserted because target node is deleted.');
        }

        if ($owner == $target) {
            throw new Exception('The target node should not be self.');
        }

        if (!$levelUp && $target->isRoot()) {
            throw new Exception('The target node should not be root.');
        }

        if ($this->hasManyRoots) {
            $owner->{$this->rootAttribute} = $target->{$this->rootAttribute};
        }

        $db = $this->getDbHandler($owner);
        $db->begin();

        try {
            $this->ignoreEvent = true;
            $this->shiftLeftRight($key, 2);
            $this->ignoreEvent = false;

            $owner->{$this->leftAttribute} = $key;
            $owner->{$this->rightAttribute} = $key + 1;
            $owner->{$this->levelAttribute} = $target->{$this->levelAttribute} + $levelUp;

            $this->ignoreEvent = true;
            $result = $owner->create($attributes);
            $this->ignoreEvent = false;

            if (!$result) {
                $db->rollback();
                $this->ignoreEvent = false;

                return false;
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            $this->ignoreEvent = false;

            throw $e;
        }

        return true;
    }

    /**
     * @param  array $attributes
     * @param  array $whiteList
     *
     * @return boolean
     * @throws Exception
     */
    private function makeRoot($attributes, $whiteList)
    {
        $owner = $this->getOwner();
        
        $owner->{$this->rootAttribute} = 0;
        $owner->{$this->leftAttribute} = 1;
        $owner->{$this->rightAttribute} = 2;
        $owner->{$this->levelAttribute} = 1;

        if ($this->hasManyRoots) {
            $this->db->begin();
            $this->ignoreEvent = true;
            if ($owner->create($attributes, $whiteList) == false) {
                $this->db->rollback();
                $this->ignoreEvent = false;

                return false;
            }

            $pk = $owner->{$this->rootAttribute} = $owner->{$this->primaryKey};
            $owner::findFirst($pk)->update(array($this->rootAttribute => $pk));
            $this->ignoreEvent = false;

            $this->db->commit();
        } else {
            if (count($owner->roots())) {
                throw new Exception('Cannot create more than one root in single root mode.');
            }

            if ($owner->create($attributes, $whiteList) == false) {
                return false;
            }
        }

        return true;
    }
}
