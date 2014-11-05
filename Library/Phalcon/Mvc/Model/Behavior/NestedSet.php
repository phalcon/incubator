<?php

namespace Phalcon\Mvc\Model\Behavior;

use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\Model;

class NestedSet extends Behavior implements BehaviorInterface
{
    private $owner;
    private $hasManyRoots = false;
    private $rootAttribute = 'root';
    private $leftAttribute = 'lft';
    private $rightAttribute = 'rgt';
    private $levelAttribute = 'level';
    private $primaryKey = 'id';
    private $ignoreEvent = false;

    private $deleted = false;

    public function __construct($options)
    {
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

    public function notify($eventType, $model)
    {
        $message = 'You should not use this method when NestedSetBehavior attached. Use the methods of behavior.';
        switch ($eventType) {
            case 'beforeCreate':
            case 'beforeDelete':
            case 'beforeUpdate':
                if (!$this->ignoreEvent) {
                    throw new \Phalcon\Mvc\Model\Exception($message);
                }
                break;
        }
    }

    public function missingMethod($model, $method, $arguments = null)
    {
        if (method_exists($this, $method)) {
            $this->setOwner($model);
            $result = call_user_func_array(array($this, $method), $arguments);
            if ($result===null) {
                return '';
            }

            return $result;
        }

        return null;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getIsNewRecord()
    {
        return $this->getOwner()->getDirtyState() == Model::DIRTY_STATE_TRANSIENT;
    }

    /**
     * Returns if the current node is deleted.
     * @return boolean whether the node is deleted.
     */
    public function getIsDeletedRecord()
    {
        return $this->deleted;
    }

    /**
     * Sets if the current node is deleted.
     * @param boolean $value whether the node is deleted.
     */
    public function setIsDeletedRecord($value)
    {
        $this->deleted=$value;
    }

    /**
     * Determines if node is leaf.
     * @return boolean whether the node is leaf.
     */
    public function isLeaf()
    {
        $owner=$this->getOwner();

        return $owner->{$this->rightAttribute}-$owner->{$this->leftAttribute}===1;
    }

    /**
     * Determines if node is root.
     * @return boolean whether the node is root.
     */
    public function isRoot()
    {
        return $this->getOwner()->{$this->leftAttribute}==1;
    }

    /**
     * Determines if node is descendant of subject node.
     * @param  \Phalcon\Mvc\ModelInterface $subj the subject node.
     * @return boolean                     whether the node is descendant of subject node.
     */
    public function isDescendantOf($subj)
    {
        $owner=$this->getOwner();
        $result=($owner->{$this->leftAttribute}>$subj->{$this->leftAttribute})
            && ($owner->{$this->rightAttribute}<$subj->{$this->rightAttribute});

        if ($this->hasManyRoots) {
            $result=$result && ($owner->{$this->rootAttribute}===$subj->{$this->rootAttribute});
        }

        return $result;
    }

    /**
     * Named scope. Gets descendants for node.
     * @param  int                                    $depth the depth.
     * @return \Phalcon\Mvc\Model\ResultsetInterface.
     */
    public function descendants($depth = null)
    {
        $owner = $this->getOwner();

        $query = $owner::query()
            ->where($this->leftAttribute . '>' . $owner->{$this->leftAttribute})
            ->andWhere($this->rightAttribute . '<' . $owner->{$this->rightAttribute})
            ->orderBy($this->leftAttribute);

        if ($depth!==null) {
            $query = $query->andWhere($this->levelAttribute.'<='.($owner->{$this->levelAttribute}+$depth));
        }

        if ($this->hasManyRoots) {
            $query = $query->andWhere($this->rootAttribute.'='.$owner->{$this->rootAttribute});
        }

        return $query->execute();
    }

    /**
     * Named scope. Gets children for node (direct descendants only).
     * @return \Phalcon\Mvc\Model\ResultsetInterface.
     */
    public function children()
    {
        return $this->descendants(1);
    }

    /**
     * Named scope. Gets ancestors for node.
     * @param  int                                    $depth the depth.
     * @return \Phalcon\Mvc\Model\ResultsetInterface.
     */
    public function ancestors($depth = null)
    {
        $owner=$this->getOwner();

        $query = $owner::query()
            ->where($this->leftAttribute . '<' . $owner->{$this->leftAttribute})
            ->andWhere($this->rightAttribute . '>' . $owner->{$this->rightAttribute})
            ->orderBy($this->leftAttribute);

        if ($depth!==null) {
            $query = $query->andWhere($this->levelAttribute.'>='.($owner->{$this->levelAttribute}-$depth));
        }

        if ($this->hasManyRoots) {
            $query = $query->andWhere($this->rootAttribute.'='.$owner->{$this->rootAttribute});
        }

        return $query->execute();
    }

    /**
     * Named scope. Gets root node(s).
     *
     * @return \Phalcon\Mvc\Model\ResultsetInterface.
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
    // @codingStandardsIgnoreStart
    public function parent()
    {
        $owner = $this->getOwner();

        $query = $owner::query()
            ->where($this->leftAttribute . '<' . $owner->{$this->leftAttribute})
            ->andWhere($this->rightAttribute . '>' . $owner->{$this->rightAttribute})
            ->orderBy($this->rightAttribute)
            ->limit(1);

        if ($this->hasManyRoots) {
            $query = $query->andWhere($this->rootAttribute.'='.$owner->{$this->rootAttribute});
        }

        return $query->execute()->getFirst();
    }
    // @codingStandardsIgnoreEnd

    /**
     * Named scope. Gets previous sibling of node.
     * @return \Phalcon\Mvc\ModelInterface.
     */
    public function prev()
    {
        $owner=$this->getOwner();
        $query = $owner::query()
            ->where($this->rightAttribute.'='.($owner->{$this->leftAttribute}-1));

        if ($this->hasManyRoots) {
            $query = $query->andWhere($this->rootAttribute.'='.$owner->{$this->rootAttribute});
        }

        return $query->execute()->getFirst();
    }

    /**
     * Named scope. Gets next sibling of node.
     * @return \Phalcon\Mvc\ModelInterface.
     */
    public function next()
    {
        $owner=$this->getOwner();
        $query = $owner::query()
            ->where($this->leftAttribute.'='.($owner->{$this->rightAttribute}+1));

        if ($this->hasManyRoots) {
            $query = $query->andWhere($this->rootAttribute.'='.$owner->{$this->rootAttribute});
        }

        return $query->execute()->getFirst();
    }

    /**
     * Prepends node to target as first child.
     *
     * @param  \Phalcon\Mvc\ModelInterface $target     the target.
     * @param  array                       $attributes list of attributes.
     * @return boolean                     whether the prepending succeeds.
     */
    public function prependTo($target, $attributes = null)
    {
        return $this->addNode($target, $target->{$this->leftAttribute}+1, 1, $attributes);
    }

    /**
     * Prepends target to node as first child.
     *
     * @param  \Phalcon\Mvc\ModelInterface $target     the target.
     * @param  array                       $attributes list of attributes.
     * @return boolean                     whether the prepending succeeds.
     */
    public function prepend($target, $attributes = null)
    {
        return $target->prependTo($this->getOwner(), $attributes);
    }

    /**
     * Appends node to target as last child.
     *
     * @param  \Phalcon\Mvc\ModelInterface $target     the target.
     * @param  array                       $attributes list of attributes.
     * @return boolean                     whether the appending succeeds.
     */
    public function appendTo($target, $attributes = null)
    {
        return $this->addNode($target, $target->{$this->rightAttribute}, 1, $attributes);
    }

    /**
     * Appends target to node as last child.
     *
     * @param  \Phalcon\Mvc\ModelInterface $target     the target.
     * @param  array                       $attributes list of attributes.
     * @return boolean                     whether the appending succeeds.
     */
    public function append($target, $attributes = null)
    {
        return $target->appendTo($this->getOwner(), $attributes);
    }

    /**
     * Inserts node as previous sibling of target.
     *
     * @param  \Phalcon\Mvc\ModelInterface $target     the target.
     * @param  array                       $attributes list of attributes.
     * @return boolean                     whether the inserting succeeds.
     */
    public function insertBefore($target, $attributes = null)
    {
        return $this->addNode($target, $target->{$this->leftAttribute}, 0, $attributes);
    }

    /**
     * Inserts node as next sibling of target.
     * @param  \Phalcon\Mvc\ModelInterface $target     the target.
     * @param  array                       $attributes list of attributes.
     * @return boolean                     whether the inserting succeeds.
     */
    public function insertAfter($target, $attributes = null)
    {
        return $this->addNode($target, $target->{$this->rightAttribute} + 1, 0, $attributes);
    }

    /**
     * Move node as previous sibling of target.
     *
     * @param  \Phalcon\Mvc\ModelInterface $target the target.
     * @return boolean                     whether the moving succeeds.
     */
    public function moveBefore($target)
    {
        return $this->moveNode($target, $target->{$this->leftAttribute}, 0);
    }

    /**
     * Move node as next sibling of target.
     *
     * @param  \Phalcon\Mvc\ModelInterface $target the target.
     * @return boolean                     whether the moving succeeds.
     */
    public function moveAfter($target)
    {
        return $this->moveNode($target, $target->{$this->rightAttribute} + 1, 0);
    }

    /**
     * Move node as first child of target.
     *
     * @param  \Phalcon\Mvc\ModelInterface $target the target.
     * @return boolean                     whether the moving succeeds.
     */
    public function moveAsFirst($target)
    {
        return $this->moveNode($target, $target->{$this->leftAttribute}+1, 1);
    }

    /**
     * Move node as last child of target.
     *
     * @param  \Phalcon\Mvc\ModelInterface $target the target.
     * @return boolean                     whether the moving succeeds.
     */
    public function moveAsLast($target)
    {
        return $this->moveNode($target, $target->{$this->rightAttribute}, 1);
    }

    /**
     * Move node as new root.
     * @return boolean                      whether the moving succeeds.
     * @throws \Phalcon\Mvc\Model\Exception
     */
    public function moveAsRoot()
    {
        $owner = $this->getOwner();

        if (!$this->hasManyRoots) {
            throw new \Phalcon\Mvc\Model\Exception('Many roots mode is off.');
        }

        if ($this->getIsNewRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node should not be new record.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node should not be deleted.');
        }

        if ($owner->isRoot()) {
            throw new \Phalcon\Mvc\Model\Exception('The node already is root node.');
        }

        $owner->getDI()->getDb()->begin();

        $left=$owner->{$this->leftAttribute};
        $right=$owner->{$this->rightAttribute};
        $levelDelta=1-$owner->{$this->levelAttribute};
        $delta=1-$left;

        $condition = $this->leftAttribute.'>='.$left.' AND ';
        $condition.= $this->rightAttribute.'<='.$right.' AND ';
        $condition.= $this->rootAttribute.'='.$owner->{$this->rootAttribute};

        $this->ignoreEvent = true;
        foreach ($owner::find($condition) as $i) {
            $arr = array(
                $this->leftAttribute=>$i->{$this->leftAttribute}+$delta,
                $this->rightAttribute=>$i->{$this->rightAttribute}+$delta,
                $this->levelAttribute=>$i->{$this->levelAttribute}+$levelDelta,
                $this->rootAttribute=>$owner->{$this->primaryKey}
            );
            if ($i->update($arr) == false) {
                $owner->getDI()->getDb()->rollback();
                $this->ignoreEvent = false;

                return false;
            }
        }
        $this->ignoreEvent = false;

        $this->shiftLeftRight($right+1, $left-$right-1);

        $owner->getDI()->getDb()->commit();

        return true;
    }

    /**
     * Create root node if multiple-root tree mode. Update node if it's not new.
     *
     * @param  array   $attributes list of attributes.
     * @param  array   $whiteList  whether to perform validation.
     * @return boolean whether the saving succeeds.
     */
    public function saveNode($attributes = null, $whiteList = null)
    {
        $owner = $this->getOwner();

        if (!$owner->readAttribute($this->primaryKey)) {
            return $this->makeRoot($attributes, $whiteList);
        }
        $this->ignoreEvent = true;
        $result = $owner->update($attributes, $whiteList);
        $this->ignoreEvent = false;

        return $result;
    }

    /**
     * Deletes node and it's descendants.
     * @return boolean                      whether the deletion is successful.
     * @throws \Phalcon\Mvc\Model\Exception
     */
    public function deleteNode()
    {
        $owner=$this->getOwner();

        if ($this->getIsNewRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be deleted because it is new.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be deleted because it is already deleted.');
        }

        $owner->getDI()->getDb()->begin();

        if ($owner->isLeaf()) {
            $this->ignoreEvent=true;
            if ($owner->delete() == false) {
                $owner->getDI()->getDb()->rollback();
                $this->ignoreEvent = false;

                return false;
            }
            $this->ignoreEvent=false;
        } else {
            $condition=$this->leftAttribute.'>='.$owner->{$this->leftAttribute}.' AND ';
            $condition.=$this->rightAttribute.'<='.$owner->{$this->rightAttribute};

            if ($this->hasManyRoots) {
                $condition.=' AND '.$this->rootAttribute.'='.$owner->{$this->rootAttribute};
            }

            $this->ignoreEvent = true;
            foreach ($owner::find($condition) as $i) {
                if ($i->delete() == false) {
                    $owner->getDI()->getDb()->rollback();
                    $this->ignoreEvent = false;

                    return false;
                }
            }
            $this->ignoreEvent = false;
        }

        $key = $owner->{$this->rightAttribute}+1;
        $delta = $owner->{$this->leftAttribute}-$owner->{$this->rightAttribute}-1;
        $this->shiftLeftRight($key, $delta);

        $owner->getDI()->getDb()->commit();

        return true;
    }

    /**
     * @param  \Phalcon\Mvc\ModelInterface  $target.
     * @param  int                          $key.
     * @param  int                          $levelUp.
     * @return boolean.
     * @throws \Phalcon\Mvc\Model\Exception
     */
    private function moveNode($target, $key, $levelUp)
    {
        $owner=$this->getOwner();

        if (!$target) {
            throw new \Phalcon\Mvc\Model\Exception('Target node is not defined.');
        }

        if ($this->getIsNewRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node should not be new record.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node should not be deleted.');
        }

        if ($target->getIsDeletedRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be deleted.');
        }

        if ($owner==$target) {
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be self.');
        }

        if ($target->isDescendantOf($owner)) {
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be descendant.');
        }

        if (!$levelUp && $target->isRoot()) {
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be root.');
        }

        $owner->getDI()->getDb()->begin();

        $left=$owner->{$this->leftAttribute};
        $right=$owner->{$this->rightAttribute};
        $levelDelta=$target->{$this->levelAttribute}-$owner->{$this->levelAttribute}+$levelUp;

        if ($this->hasManyRoots && $owner->{$this->rootAttribute}!==$target->{$this->rootAttribute}) {
            $this->ignoreEvent = true;
            foreach (array($this->leftAttribute,$this->rightAttribute) as $attribute) {
                $condition = $attribute.'>='.$key.' AND '.$this->rootAttribute.'='.$target->{$this->rootAttribute};
                foreach ($owner::find($condition) as $i) {
                    if ($i->update(array($attribute=>$i->{$attribute}+$right-$left+1)) == false) {
                        $owner->getDI()->getDb()->rollback();
                        $this->ignoreEvent = false;

                        return false;
                    }
                }
            }

            $delta=$key-$left;

            $condition = $this->leftAttribute.'>='.$left.' AND ';
            $condition .= $this->rightAttribute.'<='.$right.' AND ';
            $condition .= $this->rootAttribute.'='.$target->{$this->rootAttribute};
            foreach ($owner::find($condition) as $i) {
                $arr = array(
                    $this->leftAttribute=>$i->{$this->leftAttribute}+$delta,
                    $this->rightAttribute=>$i->{$this->rightAttribute}+$delta,
                    $this->levelAttribute=>$i->{$this->levelAttribute}+$levelDelta,
                    $this->rootAttribute=>$target->{$this->rootAttribute}
                );
                if ($i->update($arr) == false) {
                    $owner->getDI()->getDb()->rollback();
                    $this->ignoreEvent = false;

                    return false;
                }
            }
            $this->ignoreEvent = false;

            $this->shiftLeftRight($right+1, $left-$right-1);

            $owner->getDI()->getDb()->commit();
        } else {
            $delta=$right-$left+1;
            $this->shiftLeftRight($key, $delta);

            if ($left>=$key) {
                $left+=$delta;
                $right+=$delta;
            }

            $condition=$this->leftAttribute.'>='.$left.' AND '.$this->rightAttribute.'<='.$right;

            if ($this->hasManyRoots) {
                $condition.=' AND '.$this->rootAttribute.'='.$owner->{$this->rootAttribute};
            }

            $this->ignoreEvent = true;
            foreach ($owner::find($condition) as $i) {
                if ($i->update(array($this->levelAttribute=>$i->{$this->levelAttribute}+$levelDelta)) == false) {
                    $owner->getDI()->getDb()->rollback();
                    $this->ignoreEvent = false;

                    return false;
                }
            }

            foreach (array($this->leftAttribute,$this->rightAttribute) as $attribute) {
                $condition=$attribute.'>='.$left.' AND '.$attribute.'<='.$right;

                if ($this->hasManyRoots) {
                    $condition.=' AND '.$this->rootAttribute.'='.$owner->{$this->rootAttribute};
                }

                foreach ($owner::find($condition) as $i) {
                    if ($i->update(array($attribute=>$i->{$attribute}+$key-$left)) == false) {
                        $owner->getDI()->getDb()->rollback();
                        $this->ignoreEvent = false;

                        return false;
                    }
                }
            }
            $this->ignoreEvent = false;

            $this->shiftLeftRight($right+1, -$delta);

            $owner->getDI()->getDb()->commit();
        }

        return true;
    }

    /**
     * @param int $key.
     * @param int $delta.
     */
    private function shiftLeftRight($key, $delta)
    {
        $owner = $this->getOwner();

        foreach (array($this->leftAttribute, $this->rightAttribute) as $attribute) {
            $condition = $attribute.'>='.$key;

            if ($this->hasManyRoots) {
                $condition.=' AND '.$this->rootAttribute.'='.$owner->{$this->rootAttribute};
            }

            $query = sprintf(
                'UPDATE %s SET %s=%s+%d WHERE %s',
                $this->owner->getSource(),
                $attribute,
                $attribute,
                $delta,
                $condition
            );
            $this->owner->getWriteConnection()->execute($query);
        }
    }

    /**
     * @param  \Phalcon\Mvc\ModelInterface  $target.
     * @param  int                          $key.
     * @param  int                          $levelUp.
     * @param  array                        $attributes.
     * @return boolean.
     * @throws \Phalcon\Mvc\Model\Exception
     */
    private function addNode($target, $key, $levelUp, $attributes)
    {
        $owner=$this->getOwner();

        if (!$target) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be inserted because target is not defined.');
        }

        if (!$this->getIsNewRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be inserted because it is not new.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be inserted because it is deleted.');
        }

        if ($target->getIsDeletedRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be inserted because target node is deleted.');
        }

        if ($owner == $target) {
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be self.');
        }

        if (!$levelUp && $target->isRoot()) {
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be root.');
        }

        if ($this->hasManyRoots) {
            $owner->{$this->rootAttribute} = $target->{$this->rootAttribute};
        }

        $this->shiftLeftRight($key, 2);
        $owner->{$this->leftAttribute} = $key;
        $owner->{$this->rightAttribute} = $key + 1;
        $owner->{$this->levelAttribute} = $target->{$this->levelAttribute} + $levelUp;

        $this->ignoreEvent = true;
        $result = $owner->create($attributes);
        $this->ignoreEvent = false;

        return $result;
    }

    /**
     * @param  array                        $attributes.
     * @param  array                        $whiteList.
     * @return boolean.
     * @throws \Phalcon\Mvc\Model\Exception
     */
    private function makeRoot($attributes, $whiteList)
    {
        $owner = $this->getOwner();
        $owner->{$this->leftAttribute} = 1;
        $owner->{$this->rightAttribute} = 2;
        $owner->{$this->levelAttribute} = 1;

        if ($this->hasManyRoots) {
            $owner->getDI()->getDb()->begin();
            $this->ignoreEvent = true;
            if ($owner->create($attributes, $whiteList) == false) {
                $owner->getDI()->getDb()->rollback();
                $this->ignoreEvent = false;

                return false;
            }

            $pk = $owner->{$this->rootAttribute} = $owner->{$this->primaryKey};
            $owner::findFirst($pk)->update(array($this->rootAttribute=>$pk));
            $this->ignoreEvent = false;

            $owner->getDI()->getDb()->commit();
        } else {

            if (count($owner->roots())) {
                throw new \Phalcon\Mvc\Model\Exception('Cannot create more than one root in single root mode.');
            }

            if ($owner->create($attributes, $whiteList) == false) {
                return false;
            }
        }

        return true;
    }
}
