<?php
//Turn on implicit flush
ob_implicit_flush(true);

error_reporting(E_ALL);
ini_set('display_errors', 1);

//Change shell directory to current
chdir(__DIR__);

include "functions.php";

$readme = <<<'README'
    
# Adjacency list package

This package can handle hierarhical data using Adjacency list pattern (id, parent - relations).

Maintained using [package-template](https://github.com/s4urp8n/package-template)

## Testing

* php prepare.php
* php test.php

## Usage
```php

$relations=[
    [
        'id'=>...,          //===== id 
        'parent'=>...,      //===== parent id
        'data'=>...         //===== some data according to id
    ],
    ...
];

//OR

//Create a class or modify existed class to implements AdjacencyInterface and create array like this

class AdjacencyListTest implements AdjacencyInterface
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

$relations=[
    new CustomClassImplementedAdjancencyInterface(), 
    new CustomClassImplementedAdjancencyInterface(),
    new CustomClassImplementedAdjancencyInterface(),
    new CustomClassImplementedAdjancencyInterface(),
    ...
];


//Now load relations
$adjacencyList=AdjacencyList::load($relations);


//Done! Adjacency list loaded!

//Walk items recursive
$adjacencyList->walk(function($item){...});

//Find item
$item10=$adjacencyList->find(10);

//Walk item and children recursive
$item10->walk(function($item){...});

//Get parent
$parent=$item10->getParent();

//Get children
$children=$item10->getChildren();

//Get siblings
$siblings=$item10->getSiblings();

```
{{DOC_URL_HERE}}

{{COVERAGE_HERE}}

README;

return [
    'server'      => "127.0.0.1:4444",
    'packageName' => "zver/package-template",
    'readme'      => $readme,
];