<?php

class AdjacencyListTest implements \Zver\AdjacencyInterface
{

    protected $id = null;
    protected $parent = null;

    public function __construct($id, $parent)
    {
        $this->id = $id;
        $this->parent = $parent;
    }

    public function getAdjacency()
    {
        return [
            'id'     => $this->id,
            'parent' => $this->parent,
            'data'   => 'data' . $this->id,
        ];
    }
}