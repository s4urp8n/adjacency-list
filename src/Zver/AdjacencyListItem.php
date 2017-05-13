<?php

namespace Zver {

    /**
     * Class AdjacencyListItem item of AdjacencyList
     *
     * @package Zver
     */
    class AdjacencyListItem
    {

        /**
         * @var integer Id of item
         */
        protected $id;

        /**
         * @var integer Id of parent of item
         */
        protected $parentId;

        /**
         * @var mixed Some additional data
         */
        protected $data;

        /**
         * @var AdjacencyListItem Parent of item
         */
        protected $parent = null;

        /**
         * @var array Array of item children
         */
        protected $children = [];

        /**
         * AdjacencyListItem constructor. Initiate item and find it's children recursive.
         *
         * @param integer $id
         * @param integer $parentId
         * @param mixed   $data
         * @param array   $relations
         */
        public function __construct($id, $parentId, $data, &$relations)
        {
            $this->id = $id;
            $this->parentId = $parentId;
            $this->data = $data;

            foreach ($relations as $relation) {
                if (!empty($relation['parent']) && $relation['parent'] == $id) {
                    $child = new static($relation['id'], $relation['parent'], $relation['data'], $relations);
                    $child->setParent($this);
                    $this->children[] = $child;
                }
            }
        }

        /**
         * Walk item and all it's children recursive
         *
         * @param callable $callback
         *
         * @return $this
         */
        public function walk($callback)
        {
            $callback($this);
            if ($this->haveChildren()) {
                foreach ($this->getChildren() as $children) {
                    $children->walk($callback);
                }
            }

            return $this;
        }

        /**
         * Return true if item have child, false otherwise.
         * If $childrenId argument passed return true if item have child with id equals $childrenId, false otherwise.
         *
         * @param integer|null $childrenId
         *
         * @return bool
         */
        public function haveChildren($childrenId = null)
        {
            if (!empty($this->children)) {

                if (is_null($childrenId)) {
                    return true;
                } else {
                    return in_array($childrenId, $this->getRecursiveChildrenIds());
                }
            }

            return false;
        }

        /**
         * Get item children array
         *
         * @return array
         */
        public function getChildren()
        {
            return $this->children;
        }

        /**
         * Get item children ids array
         *
         * @return array
         */
        public function getChildrenIds()
        {
            return array_map(
                function ($value) {
                    return $value->getId();
                }, $this->children
            );
        }

        /**
         * Return id as string if item uses as string
         *
         * @return string
         */
        public function __toString()
        {
            return (string)$this->getId();
        }

        /**
         * Returns item id
         *
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Get item data
         *
         * @return mixed
         */
        public function getData()
        {
            return $this->data;
        }

        /**
         * Get ids of children of item recursive (all levels)
         *
         * @return array
         */
        public function getRecursiveChildrenIds()
        {
            $children = array_map(
                function ($element) {
                    return $element->getId();
                }, $this->getRecursiveChildren()
            );

            return $children;
        }

        /**
         * Get children of item recursive (all levels)
         *
         * @return array
         */
        public function getRecursiveChildren()
        {
            $children = [];

            if ($this->haveChildren()) {
                foreach ($this->getChildren() as $child) {
                    $children[$child->getId()] = $child;
                    $children = array_merge($children, $child->getRecursiveChildren());
                }
            }

            return array_values($children);
        }

        /**
         * Get id of root parent of item
         *
         * @return int
         */
        public function getRootParentId()
        {
            return $this->getRootParent()
                        ->getId();
        }

        /**
         * Get root parent of item
         *
         * @return $this|\Zver\AdjacencyListItem
         */
        public function getRootParent()
        {
            if ($this->haveParent()) {
                $parent = $this->getParent();
                while ($parent->haveParent()) {
                    $parent = $parent->getParent();
                }

                return $parent;
            } else {
                return $this;
            }
        }

        /**
         * Return true if item have parent, false otherwise
         *
         * @return bool
         */
        public function haveParent()
        {
            return !empty($this->parent);
        }

        /**
         * Return parent of item
         *
         * @return \Zver\AdjacencyListItem|null
         */
        public function getParent()
        {
            return $this->parent;
        }

        /**
         * Return id of parent
         *
         * @return int|null
         */
        public function getParentId()
        {
            if ($this->haveParent()) {
                return $this->parentId;
            }

            return null;
        }

        /**
         * Set parent of item by reference to fast access
         *
         * @param $parent
         */
        protected function setParent(&$parent)
        {
            $this->parent = $parent;
        }

        /**
         * Get level of item. 0 - means that list is empty, 1 - means that is branch root, ...
         * Then greater level then item is deeper into hierarchy.
         *
         * @return int
         */
        public function getLevel()
        {
            $currentParent = $this->getParent();
            $level = 1;
            while (!empty($currentParent)) {
                $level++;
                $currentParent = $currentParent->getParent();
            }

            return $level;
        }

        /**
         * Get array of item siblings (having same level in current branch)
         *
         * @return array
         */
        public function getBranchSiblings()
        {
            $siblings = [];

            $currentLevel = $this->getLevel();

            if ($currentLevel > 1) {
                $this->getParent()
                     ->walk(
                         function ($item) use (&$siblings, $currentLevel) {
                             if ($item->getLevel() == $currentLevel && $item->getId() !== $this->getId()) {
                                 $siblings[] = $item;
                             }
                         }
                     );
            }

            return $siblings;
        }

        /**
         * Get array of item siblings ids (having same level in current branch)
         *
         * @return array
         */
        public function getBranchSiblingsIds()
        {
            return array_map(
                function ($item) {
                    return $item->getId();
                }, $this->getBranchSiblings()
            );
        }
    }
}
