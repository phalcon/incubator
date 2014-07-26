<?php

namespace Phalcon\Mvc\Model\Behavior;

use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\Model;

class NestedSet extends Behavior implements BehaviorInterface {
    private $_owner;
    private $_hasManyRoots = false;
    private $_rootAttribute = 'root';
    private $_leftAttribute = 'lft';
    private $_rightAttribute = 'rgt';
    private $_levelAttribute = 'level';
    private $_primaryKey = 'id';
    private $_ignoreEvent = false;

    private $_deleted = false;


    public function __construct($options)
    {
        if (isset($options['hasManyRoots'])) {
            $this->_rootAttribute = (bool) $options['hasManyRoots'];
        }

        if (isset($options['rootAttribute'])) {
            $this->_rootAttribute = $options['rootAttribute'];
        }

        if (isset($options['leftAttribute'])) {
            $this->_leftAttribute = $options['leftAttribute'];
        }

        if (isset($options['rightAttribute'])) {
            $this->_rightAttribute = $options['rightAttribute'];
        }

        if (isset($options['levelAttribute'])) {
            $this->_levelAttribute = $options['levelAttribute'];
        }

        if (isset($options['primaryKey'])) {
            $this->_primaryKey = $options['primaryKey'];
        }
    }

    public function notify($eventType, $model)
    {
        switch ($eventType) {
            case 'beforeCreate':
            case 'beforeDelete':
            case 'beforeUpdate':
                if(!$this->_ignoreEvent)
                throw new \Phalcon\Mvc\Model\Exception('You should not use this method when NestedSetBehavior attached. Use the methods of behavior.');
                break;
        }
    }

    public function missingMethod($model, $method, $arguments=null)
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
        return $this->_owner;
    }

    public function setOwner($owner)
    {
        $this->_owner = $owner;
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
        return $this->_deleted;
    }

    /**
     * Sets if the current node is deleted.
     * @param boolean $value whether the node is deleted.
     */
    public function setIsDeletedRecord($value)
    {
        $this->_deleted=$value;
    }

    /**
     * Determines if node is leaf.
     * @return boolean whether the node is leaf.
     */
    public function isLeaf()
    {
        $owner=$this->getOwner();

        return $owner->{$this->_rightAttribute}-$owner->{$this->_leftAttribute}===1;
    }

    /**
     * Determines if node is root.
     * @return boolean whether the node is root.
     */
    public function isRoot()
    {
        return $this->getOwner()->{$this->_leftAttribute}==1;
    }

    /**
     * Determines if node is descendant of subject node.
     * @param \Phalcon\Mvc\ModelInterface $subj the subject node.
     * @return boolean whether the node is descendant of subject node.
     */
    public function isDescendantOf($subj)
    {
        $owner=$this->getOwner();
        $result=($owner->{$this->_leftAttribute}>$subj->{$this->_leftAttribute})
            && ($owner->{$this->_rightAttribute}<$subj->{$this->_rightAttribute});

        if($this->_hasManyRoots)
            $result=$result && ($owner->{$this->_rootAttribute}===$subj->{$this->_rootAttribute});

        return $result;
    }

    /**
     * Named scope. Gets descendants for node.
     * @param int $depth the depth.
     * @return  \Phalcon\Mvc\Model\ResultsetInterface.
     */
    public function descendants($depth=null)
    {
        $owner = $this->getOwner();

        $query = $owner::query()
            ->where($this->_leftAttribute . '>' . $owner->{$this->_leftAttribute})
            ->andWhere($this->_rightAttribute . '<' . $owner->{$this->_rightAttribute})
            ->orderBy($this->_leftAttribute);

        if ($depth!==null) {
            $query = $query->andWhere($this->_levelAttribute.'<='.($owner->{$this->_levelAttribute}+$depth));
        }

        if($this->_hasManyRoots) {
            $query = $query->andWhere($this->_rootAttribute.'='.$owner->{$this->_rootAttribute});
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
     * @param int $depth the depth.
     * @return \Phalcon\Mvc\Model\ResultsetInterface.
     */
    public function ancestors($depth=null)
    {
        $owner=$this->getOwner();

        $query = $owner::query()
            ->where($this->_leftAttribute . '<' . $owner->{$this->_leftAttribute})
            ->andWhere($this->_rightAttribute . '>' . $owner->{$this->_rightAttribute})
            ->orderBy($this->_leftAttribute);

        if ($depth!==null) {
            $query = $query->andWhere($this->_levelAttribute.'>='.($owner->{$this->_levelAttribute}-$depth));
        }

        if($this->_hasManyRoots) {
            $query = $query->andWhere($this->_rootAttribute.'='.$owner->{$this->_rootAttribute});
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
        return $owner::find($this->_leftAttribute . ' = 1');
    }

    /**
     * Named scope. Gets parent of node.
     *
     * @return \Phalcon\Mvc\ModelInterface.
     */
    public function parent()
    {
        $owner = $this->getOwner();

        $query = $owner::query()
            ->where($this->_leftAttribute . '<' . $owner->{$this->_leftAttribute})
            ->andWhere($this->_rightAttribute . '>' . $owner->{$this->_rightAttribute})
            ->orderBy($this->_rightAttribute)
            ->limit(1);

        if($this->_hasManyRoots) {
            $query = $query->andWhere($this->_rootAttribute.'='.$owner->{$this->_rootAttribute});
        }

        return $query->execute()->getFirst();
    }

    /**
     * Named scope. Gets previous sibling of node.
     * @return \Phalcon\Mvc\ModelInterface.
     */
    public function prev()
    {
        $owner=$this->getOwner();
        $query = $owner::query()
            ->where($this->_rightAttribute.'='.($owner->{$this->_leftAttribute}-1));

        if($this->_hasManyRoots) {
            $query = $query->andWhere($this->_rootAttribute.'='.$owner->{$this->_rootAttribute});
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
            ->where($this->_leftAttribute.'='.($owner->{$this->_rightAttribute}+1));

        if($this->_hasManyRoots) {
            $query = $query->andWhere($this->_rootAttribute.'='.$owner->{$this->_rootAttribute});
        }

        return $query->execute()->getFirst();
    }

    /**
     * Prepends node to target as first child.
     *
     * @param \Phalcon\Mvc\ModelInterface $target the target.
     * @param array $attributes list of attributes.
     * @return boolean whether the prepending succeeds.
     */
    public function prependTo($target, $attributes=null)
    {
        return $this->addNode($target,$target->{$this->_leftAttribute}+1, 1, $attributes);
    }

    /**
     * Prepends target to node as first child.
     *
     * @param \Phalcon\Mvc\ModelInterface $target the target.
     * @param array $attributes list of attributes.
     * @return boolean whether the prepending succeeds.
     */
    public function prepend($target, $attributes=null)
    {
        return $target->prependTo($this->getOwner(), $attributes);
    }

    /**
     * Appends node to target as last child.
     *
     * @param \Phalcon\Mvc\ModelInterface $target the target.
     * @param array $attributes list of attributes.
     * @return boolean whether the appending succeeds.
     */
    public function appendTo($target, $attributes=null)
    {
        return $this->addNode($target, $target->{$this->_rightAttribute}, 1, $attributes);
    }

    /**
     * Appends target to node as last child.
     *
     * @param \Phalcon\Mvc\ModelInterface $target the target.
     * @param array $attributes list of attributes.
     * @return boolean whether the appending succeeds.
     */
    public function append($target, $attributes=null)
    {
        return $target->appendTo($this->getOwner(), $attributes);
    }

    /**
     * Inserts node as previous sibling of target.
     *
     * @param \Phalcon\Mvc\ModelInterface $target the target.
     * @param array $attributes list of attributes.
     * @return boolean whether the inserting succeeds.
     */
    public function insertBefore($target, $attributes=null)
    {
        return $this->addNode($target, $target->{$this->_leftAttribute}, 0, $attributes);
    }

    /**
     * Inserts node as next sibling of target.
     * @param \Phalcon\Mvc\ModelInterface $target the target.
     * @param array $attributes list of attributes.
     * @return boolean whether the inserting succeeds.
     */
    public function insertAfter($target,$attributes=null)
    {
        return $this->addNode($target, $target->{$this->_rightAttribute} + 1, 0, $attributes);
    }

    /**
     * Move node as previous sibling of target.
     *
     * @param \Phalcon\Mvc\ModelInterface $target the target.
     * @return boolean whether the moving succeeds.
     */
    public function moveBefore($target)
    {
        return $this->moveNode($target, $target->{$this->_leftAttribute}, 0);
    }

    /**
     * Move node as next sibling of target.
     *
     * @param \Phalcon\Mvc\ModelInterface $target the target.
     * @return boolean whether the moving succeeds.
     */
    public function moveAfter($target)
    {
        return $this->moveNode($target, $target->{$this->_rightAttribute} + 1, 0);
    }

    /**
     * Move node as first child of target.
     *
     * @param \Phalcon\Mvc\ModelInterface $target the target.
     * @return boolean whether the moving succeeds.
     */
    public function moveAsFirst($target)
    {
        return $this->moveNode($target, $target->{$this->_leftAttribute}+1, 1);
    }

    /**
     * Move node as last child of target.
     *
     * @param \Phalcon\Mvc\ModelInterface $target the target.
     * @return boolean whether the moving succeeds.
     */
    public function moveAsLast($target)
    {
        return $this->moveNode($target, $target->{$this->_rightAttribute}, 1);
    }

    /**
     * Move node as new root.
     * @return boolean whether the moving succeeds.
     * @throws \Phalcon\Mvc\Model\Exception
     */
    public function moveAsRoot()
    {
        $owner = $this->getOwner();

        if (!$this->_hasManyRoots) {
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

        $left=$owner->{$this->_leftAttribute};
        $right=$owner->{$this->_rightAttribute};
        $levelDelta=1-$owner->{$this->_levelAttribute};
        $delta=1-$left;

        $this->_ignoreEvent = true;
        foreach($owner::find($this->_leftAttribute.'>='.$left.' AND '.$this->_rightAttribute.'<='.$right.' AND '.$this->_rootAttribute.'='.$owner->{$this->_rootAttribute}) as $i) {
            if($i->update(array($this->_leftAttribute=>$i->{$this->_leftAttribute}+$delta, $this->_rightAttribute=>$i->{$this->_rightAttribute}+$delta, $this->_levelAttribute=>$i->{$this->_levelAttribute}+$levelDelta, $this->_rootAttribute=>$owner->{$this->_primaryKey})) == false) {
                $owner->getDI()->getDb()->rollback();
                $this->_ignoreEvent = false;
                return false;
            }
        }
        $this->_ignoreEvent = false;

        $this->shiftLeftRight($right+1,$left-$right-1);

        $owner->getDI()->getDb()->commit();

        return true;
    }

    /**
     * Create root node if multiple-root tree mode. Update node if it's not new.
     *
     * @param array $attributes list of attributes.
     * @param array $whiteList whether to perform validation.
     * @return boolean whether the saving succeeds.
     */
    public function saveNode($attributes=null, $whiteList=null)
    {
        $owner = $this->getOwner();

        if (!$owner->id) {
            return $this->makeRoot($attributes, $whiteList);
        }
        $this->_ignoreEvent = true;
        $result = $owner->update($attributes, $whiteList);
        $this->_ignoreEvent = false;

        return $result;
    }

    /**
     * Deletes node and it's descendants.
     * @return boolean whether the deletion is successful.
     * @throws \Phalcon\Mvc\Model\Exception
     */
    public function deleteNode()
    {
        $owner=$this->getOwner();

        if ($this->getIsNewRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be deleted because it is new.');
        }

        if($this->getIsDeletedRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be deleted because it is already deleted.');
        }

        $owner->getDI()->getDb()->begin();


        if($owner->isLeaf())
        {
            $this->_ignoreEvent=true;
            if($owner->delete() == false) {
                $owner->getDI()->getDb()->rollback();
                $this->_ignoreEvent = false;
                return false;
            }
            $this->_ignoreEvent=false;
        }
        else
        {
            $condition=$this->_leftAttribute.'>='.$owner->{$this->_leftAttribute}.' AND '.$this->_rightAttribute.'<='.$owner->{$this->_rightAttribute};

            if($this->_hasManyRoots)
            {
                $condition.=' AND '.$this->_rootAttribute.'='.$owner->{$this->_rootAttribute};
            }

            $this->_ignoreEvent = true;
            foreach($owner::find($condition) as $i) {
                if($i->delete() == false) {
                    $owner->getDI()->getDb()->rollback();
                    $this->_ignoreEvent = false;
                    return false;
                }
            }
            $this->_ignoreEvent = false;
        }

        $this->shiftLeftRight($owner->{$this->_rightAttribute}+1,$owner->{$this->_leftAttribute}-$owner->{$this->_rightAttribute}-1);

        $owner->getDI()->getDb()->commit();

        return true;
    }


    /**
     * @param \Phalcon\Mvc\ModelInterface $target.
     * @param int $key.
     * @param int $levelUp.
     * @return boolean.
     * @throws \Phalcon\Mvc\Model\Exception
     */
    private function moveNode($target,$key,$levelUp)
    {
        $owner=$this->getOwner();

        if(!$target)
            throw new \Phalcon\Mvc\Model\Exception('Target node is not defined.');

        if($this->getIsNewRecord())
            throw new \Phalcon\Mvc\Model\Exception('The node should not be new record.');

        if($this->getIsDeletedRecord())
            throw new \Phalcon\Mvc\Model\Exception('The node should not be deleted.');

        if($target->getIsDeletedRecord())
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be deleted.');

        if($owner==$target)
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be self.');

        if($target->isDescendantOf($owner))
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be descendant.');

        if(!$levelUp && $target->isRoot())
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be root.');

        $owner->getDI()->getDb()->begin();

        $left=$owner->{$this->_leftAttribute};
        $right=$owner->{$this->_rightAttribute};
        $levelDelta=$target->{$this->_levelAttribute}-$owner->{$this->_levelAttribute}+$levelUp;

        if($this->_hasManyRoots && $owner->{$this->_rootAttribute}!==$target->{$this->_rootAttribute})
        {
            $this->_ignoreEvent = true;
            foreach(array($this->_leftAttribute,$this->_rightAttribute) as $attribute)
            {
                foreach($owner::find($attribute.'>='.$key.' AND '.$this->_rootAttribute.'='.$target->{$this->_rootAttribute}) as $i) {
                    if($i->update(array($attribute=>$i->{$attribute}+$right-$left+1)) == false) {
                        $owner->getDI()->getDb()->rollback();
                        $this->_ignoreEvent = false;
                        return false;
                    }
                }
            }

            $delta=$key-$left;

            foreach($owner::find($this->_leftAttribute.'>='.$left.' AND '.$this->_rightAttribute.'<='.$right.' AND '.$this->_rootAttribute.'='.$target->{$this->_rootAttribute}) as $i) {
                if($i->update(array($this->_leftAttribute=>$i->{$this->_leftAttribute}+$delta, $this->_rightAttribute=>$i->{$this->_rightAttribute}+$delta, $this->_levelAttribute=>$i->{$this->_levelAttribute}+$levelDelta, $this->_rootAttribute=>$target->{$this->_rootAttribute})) == false) {
                    $owner->getDI()->getDb()->rollback();
                    $this->_ignoreEvent = false;
                    return false;
                }
            }
            $this->_ignoreEvent = false;

            $this->shiftLeftRight($right+1,$left-$right-1);

            $owner->getDI()->getDb()->commit();
        }
        else
        {
            $delta=$right-$left+1;
            $this->shiftLeftRight($key,$delta);

            if($left>=$key)
            {
                $left+=$delta;
                $right+=$delta;
            }

            $condition=$this->_leftAttribute.'>='.$left.' AND '.$this->_rightAttribute.'<='.$right;

            if($this->_hasManyRoots)
            {
                $condition.=' AND '.$this->_rootAttribute.'='.$owner->{$this->_rootAttribute};
            }

            $this->_ignoreEvent = true;
            foreach($owner::find($condition) as $i) {
                if($i->update(array($this->_levelAttribute=>$i->{$this->_levelAttribute}+$levelDelta)) == false) {
                    $owner->getDI()->getDb()->rollback();
                    $this->_ignoreEvent = false;
                    return false;
                }
            }

            foreach(array($this->_leftAttribute,$this->_rightAttribute) as $attribute)
            {
                $condition=$attribute.'>='.$left.' AND '.$attribute.'<='.$right;

                if($this->_hasManyRoots)
                {
                    $condition.=' AND '.$this->_rootAttribute.'='.$owner->{$this->_rootAttribute};
                }

                foreach($owner::find($condition) as $i) {
                    if($i->update(array($attribute=>$i->{$attribute}+$key-$left)) == false) {
                        $owner->getDI()->getDb()->rollback();
                        $this->_ignoreEvent = false;
                        return false;
                    }
                }
            }
            $this->_ignoreEvent = false;

            $this->shiftLeftRight($right+1,-$delta);

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

        foreach(array($this->_leftAttribute, $this->_rightAttribute) as $attribute)
        {
            $condition = $attribute.'>='.$key;

            if($this->_hasManyRoots)
            {
                $condition.=' AND '.$this->_rootAttribute.'='.$owner->{$this->_rootAttribute};
            }

            $this->_ignoreEvent = true;
            foreach ($owner::find($condition) as $record) {
                $record->update(array($attribute=>$record->{$attribute}+$delta));
            }
            $this->_ignoreEvent = false;
        }
    }

    /**
     * @param \Phalcon\Mvc\ModelInterface $target.
     * @param int $key.
     * @param int $levelUp.
     * @param array $attributes.
     * @return boolean.
     * @throws \Phalcon\Mvc\Model\Exception
     */
    private function addNode($target,$key,$levelUp,$attributes)
    {
        $owner=$this->getOwner();

        if(!$target)
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be inserted because target is not defined.');

        if (!$this->getIsNewRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be inserted because it is not new.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be inserted because it is deleted.');
        }

        if ($target->getIsDeletedRecord()) {
            throw new \Phalcon\Mvc\Model\Exception('The node cannot be inserted because target node is deleted.');
        }

        if($owner == $target) {
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be self.');
        }

        if(!$levelUp && $target->isRoot()) {
            throw new \Phalcon\Mvc\Model\Exception('The target node should not be root.');
        }

        if($this->_hasManyRoots) {
            $owner->{$this->_rootAttribute} = $target->{$this->_rootAttribute};
        }

        $this->shiftLeftRight($key, 2);
        $owner->{$this->_leftAttribute} = $key;
        $owner->{$this->_rightAttribute} = $key + 1;
        $owner->{$this->_levelAttribute} = $target->{$this->_levelAttribute} + $levelUp;

        $this->_ignoreEvent = true;
        $result = $owner->create($attributes);
        $this->_ignoreEvent = false;

        return $result;
    }

    /**
     * @param array $attributes.
     * @param array $whiteList.
     * @return boolean.
     * @throws \Phalcon\Mvc\Model\Exception
     */
    private function makeRoot($attributes, $whiteList)
    {
        $owner = $this->getOwner();
        $owner->{$this->_leftAttribute} = 1;
        $owner->{$this->_rightAttribute} = 2;
        $owner->{$this->_levelAttribute} = 1;

        if($this->_hasManyRoots) {
            $owner->getDI()->getDb()->begin();
            $this->_ignoreEvent = true;
            if ($owner->create($attributes, $whiteList) == false) {
                $owner->getDI()->getDb()->rollback();
                $this->_ignoreEvent = false;
                return false;
            }

            $pk = $owner->{$this->_rootAttribute} = $owner->{$this->_primaryKey};
            $owner::findFirst($pk)->update(array($this->_rootAttribute=>$pk));
            $this->_ignoreEvent = false;

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