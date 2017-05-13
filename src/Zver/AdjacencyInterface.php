<?php

namespace Zver {

    /**
     * Interface AdjacencyInterface to handle hierarhical data of implemented class
     *
     * @package Zver
     */
    interface AdjacencyInterface
    {

        /**
         * This method must return array like:
         *
         * [
         *      'id'=>..., <--- id
         *      'parent'=>..., <-- parent id or null
         *      'data'=>..., <-- some data according to id
         * ]
         *
         * @return array
         */
        public function getAdjacency();
    }
}
