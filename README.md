    
# Adjacency list package

This package can handle hierarhical data using Adjacency list pattern (id, parent - relations).

Maintained using [package-template](https://github.com/s4urp8n/package-template)


## Install
```
composer require zver/adjacency-list
```

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

class CustomClass implements AdjacencyInterface
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
    new CustomClass(1,null), 
    new CustomClass(2,1),
    new CustomClass(3,1),
    new CustomClass(4,2),
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
[Documentation](https://s4urp8n.github.io/adjacency-list/index.html)

```

Code Coverage Report:       
  2016-08-10 16:08:35       
                            
 Summary:                   
  Classes: 100.00% (3/3)    
  Methods: 100.00% (30/30)  
  Lines:   100.00% (149/149)

\Zver::AdjacencyList
  Methods: 100.00% (10/10)   Lines: 100.00% ( 55/ 55)
\Zver::AdjacencyListItem
  Methods: 100.00% (20/20)   Lines: 100.00% ( 91/ 91)
```
