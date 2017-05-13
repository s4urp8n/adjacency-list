<?php

namespace Zver {

    /**
     * Class AdjacencyList to handle hierarchical data using Adjancency list pattern
     *
     * @package Zver
     */
    class AdjacencyList
    {

        /**
         * Array to store all hierarhy elements
         *
         * @var array
         */
        protected $items = [];

        /**
         * AdjacencyList constructor.
         * Loads elements according it's hierarhy relations.
         *
         * @param array $items
         *
         */
        protected function __construct(array $items)
        {
            if (!empty($items)) {

                $items = array_values($items);

                if ($items[0] instanceof AdjacencyInterface) {
                    $items = array_map(
                        function ($item) {
                            return $item->getAdjacency();
                        }, $items
                    );
                }

                foreach ($items as $item) {
                    $data = $item;

                    if (array_key_exists('id', $data) && (array_key_exists('parent', $data))
                        && array_key_exists(
                            'data', $data
                        )
                    ) {
                        if (empty($data['parent'])) {
                            $this->add($data['id'], $data['parent'], $data['data'], $items);
                        }
                    } else {
                        throw new \Exception(__CLASS__ . ' item must contain associative array with keys: "id", "parent" and "data"');
                    }
                }
            }
        }

        /**
         * Load items and return instance of AdjacencyList
         *
         * @param array $items
         *
         * @return static
         */
        public static function load(array $items)
        {
            return new static($items);
        }

        /**
         * Add item into list as AdjacencyListItem
         *
         * @param integer $id        Id of item
         * @param integer $parent    Id of parent of item
         * @param mixed   $data      Some additional data
         * @param array   $relations Initial relations array
         *
         * @return $this
         */
        protected function add($id, $parent, $data, &$relations)
        {
            $this->items[] = new AdjacencyListItem($id, $parent, $data, $relations);

            return $this;
        }

        /**
         * Alias for getList()
         *
         * @see getList()
         * @return array
         */
        public function getItems()
        {
            return $this->items;
        }

        /**
         * Get loaded hierarhy list
         *
         * @return array
         */
        public function getList()
        {
            return $this->items;
        }

        /**
         * Find item in list using id and returns it.
         * If item not found returns false.
         *
         * @param integer $id
         *
         * @return AdjacencyListItem|bool
         */
        public function find($id)
        {
            $found = false;
            $this->walk(
                function ($element) use (&$found, $id) {
                    if ($element->getId() == $id) {
                        $found = $element;
                    }
                }
            );

            return $found;
        }

        /**
         * Walk recursive all items and execute callback
         *
         * @param callable $callback
         *
         * @return $this
         */
        public function walk($callback)
        {
            foreach ($this->items as $item) {
                $item->walk($callback);
            }

            return $this;
        }

        /**
         * Get max item level in list
         *
         * @return int
         */
        public function getMaxLevel()
        {
            $max = 0;
            $this->walk(
                function ($element) use (&$max) {
                    $level = $element->getLevel();
                    if ($level > $max) {
                        $max = $level;
                    }
                }
            );

            return $max;
        }

        /**
         * Return true if list is empty, false otherwise.
         *
         * @return bool
         */
        public function isEmpty()
        {
            return empty($this->items);
        }

    }
}
