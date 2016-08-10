<?php

class AdjacencyUnitCest
{
    
    public function testException(UnitTester $I)
    {
        $I->expectException(
            '\Zver\Exceptions\AdjacencyList\InvalidAdjacencyArgumentException', function ()
        {
            \Zver\AdjacencyList::load([['str']]);
        }
        );
    }
    
    public function testEmpty(UnitTester $I)
    {
        $adjacencyList = \Zver\AdjacencyList::load([]);
        
        $I->assertFalse($adjacencyList->find(1));
        $I->assertFalse($adjacencyList->find(2));
        $I->assertFalse($adjacencyList->find(3));
        $I->assertFalse($adjacencyList->find(9));
        $I->assertFalse($adjacencyList->find(100));
        
        $I->assertSame($adjacencyList->getMaxLevel(), 0);
        $I->assertSame($adjacencyList->getList(), []);
        $I->assertSame($adjacencyList->getItems(), []);
        $I->assertTrue($adjacencyList->isEmpty());
        
        $adjacencyList->walk(
            function ()
            {
                
            }
        );
        
    }
    
    public function testLoad(UnitTester $I)
    {
        
        /**
         *  Tree in this test
         *
         *    1      2      6
         *    |     / \
         *    3    4   5
         *            /
         *           7
         *          /
         *         8
         *
         */
        
        $testListArray = [
            [
                'id'     => 1,
                'data'   => 'data1',
                'parent' => null,
            ],
            [
                'id'     => 2,
                'data'   => 'data2',
                'parent' => null,
            ],
            [
                'id'     => 3,
                'data'   => 'data3',
                'parent' => 1,
            ],
            [
                'id'     => 4,
                'data'   => 'data4',
                'parent' => 2,
            ],
            [
                'id'     => 5,
                'data'   => 'data5',
                'parent' => 2,
            ],
            [
                'id'     => 6,
                'data'   => 'data6',
                'parent' => null,
            ],
            [
                'id'     => 7,
                'data'   => 'data7',
                'parent' => 5,
            ],
            [
                'id'     => 8,
                'data'   => 'data8',
                'parent' => 7,
            ],
        
        ];
        
        $testListClassFromInterface = [
            new AdjacencyListTest(1, null),
            new AdjacencyListTest(2, null),
            new AdjacencyListTest(3, 1),
            new AdjacencyListTest(4, 2),
            new AdjacencyListTest(5, 2),
            new AdjacencyListTest(6, null),
            new AdjacencyListTest(7, 5),
            new AdjacencyListTest(8, 7),
        ];
        
        $unexistedId = 99;
        
        foreach ([$testListArray, $testListClassFromInterface] as $testedList)
        {
            
            $adjacencyList = \Zver\AdjacencyList::load($testedList);
            
            $I->assertFalse($adjacencyList->isEmpty());
            $I->assertSame($adjacencyList->getMaxLevel(), 4);
            
            $I->assertNotEmpty($adjacencyList->getItems());
            $I->assertNotEmpty($adjacencyList->getList());
            
            foreach ($testListArray as $originArray)
            {
                /** @var \Zver\AdjacencyListItem $found */
                
                $found = $adjacencyList->find($originArray['id']);
                
                $I->assertFalse($adjacencyList->find($unexistedId));
                
                $I->assertNotEmpty($found);
                $I->assertSame($found->getId(), $originArray['id']);
                $I->assertSame($found . '', (string)$originArray['id']);
                $I->assertSame($found->getData(), $originArray['data']);
                $I->assertInstanceOf('\Zver\AdjacencyListItem', $found);
                
                $haveParent = $found->haveParent();
                
                if (empty($originArray['parent']))
                {
                    $I->assertFalse($haveParent);
                }
                else
                {
                    $I->assertTrue($haveParent);
                    
                    /** @var \Zver\AdjacencyListItem $parent */
                    
                    $parent = $adjacencyList->find($found->getParentId());
                    
                    $I->assertNotEmpty($parent);
                    $I->assertSame($parent->getId(), $originArray['parent']);
                    $I->assertInstanceOf('\Zver\AdjacencyListItem', $parent);
                    $I->assertTrue($parent->haveChildren());
                    $I->assertFalse($parent->haveChildren($unexistedId));
                    $I->assertTrue($parent->haveChildren($found->getId()));
                }
                
                switch ($found->getId())
                {
                    
                    case 1:
                    {
                        $I->assertSame($found->getId(), 1);
                        $I->assertSame($found->getData(), 'data1');
                        $I->assertSame($found->getParentId(), null);
                        $I->assertSame($found->getChildrenIds(), [3]);
                        $I->assertSame($found->getRecursiveChildrenIds(), [3]);
                        $I->assertSame($found->getRootParentId(), 1);
                        $I->assertSame($found->getLevel(), 1);
                        $I->assertSame($found->getBranchSiblingsIds(), []);
                        break;
                    }
                    case 2:
                    {
                        $I->assertSame($found->getId(), 2);
                        $I->assertSame($found->getData(), 'data2');
                        $I->assertSame($found->getParentId(), null);
                        $I->assertSame($found->getChildrenIds(), [4, 5]);
                        $I->assertSame($found->getRecursiveChildrenIds(), [4, 5, 7, 8]);
                        $I->assertSame($found->getRootParentId(), 2);
                        $I->assertSame($found->getLevel(), 1);
                        $I->assertSame($found->getBranchSiblingsIds(), []);
                        break;
                    }
                    case 3:
                    {
                        $I->assertSame($found->getId(), 3);
                        $I->assertSame($found->getData(), 'data3');
                        $I->assertSame($found->getParentId(), 1);
                        $I->assertSame($found->getChildrenIds(), []);
                        $I->assertSame($found->getRecursiveChildrenIds(), []);
                        $I->assertSame($found->getRootParentId(), 1);
                        $I->assertSame($found->getLevel(), 2);
                        $I->assertSame($found->getBranchSiblingsIds(), []);
                        break;
                    }
                    case 4:
                    {
                        $I->assertSame($found->getId(), 4);
                        $I->assertSame($found->getData(), 'data4');
                        $I->assertSame($found->getParentId(), 2);
                        $I->assertSame($found->getChildrenIds(), []);
                        $I->assertSame($found->getRecursiveChildrenIds(), []);
                        $I->assertSame($found->getRootParentId(), 2);
                        $I->assertSame($found->getLevel(), 2);
                        $I->assertSame($found->getBranchSiblingsIds(), [5]);
                        break;
                    }
                    case 5:
                    {
                        $I->assertSame($found->getId(), 5);
                        $I->assertSame($found->getData(), 'data5');
                        $I->assertSame($found->getParentId(), 2);
                        $I->assertSame($found->getChildrenIds(), [7]);
                        $I->assertSame($found->getRecursiveChildrenIds(), [7, 8]);
                        $I->assertSame($found->getRootParentId(), 2);
                        $I->assertSame($found->getLevel(), 2);
                        $I->assertSame($found->getBranchSiblingsIds(), [4]);
                        break;
                    }
                    case 6:
                    {
                        $I->assertSame($found->getId(), 6);
                        $I->assertSame($found->getData(), 'data6');
                        $I->assertSame($found->getParentId(), null);
                        $I->assertSame($found->getChildrenIds(), []);
                        $I->assertSame($found->getRecursiveChildrenIds(), []);
                        $I->assertSame($found->getRootParentId(), 6);
                        $I->assertSame($found->getLevel(), 1);
                        $I->assertSame($found->getBranchSiblingsIds(), []);
                        break;
                    }
                    case 7:
                    {
                        $I->assertSame($found->getId(), 7);
                        $I->assertSame($found->getData(), 'data7');
                        $I->assertSame($found->getParentId(), 5);
                        $I->assertSame($found->getChildrenIds(), [8]);
                        $I->assertSame($found->getRecursiveChildrenIds(), [8]);
                        $I->assertSame($found->getRootParentId(), 2);
                        $I->assertSame($found->getLevel(), 3);
                        $I->assertSame($found->getBranchSiblingsIds(), []);
                        break;
                    }
                    case 8:
                    {
                        $I->assertSame($found->getId(), 8);
                        $I->assertSame($found->getData(), 'data8');
                        $I->assertSame($found->getParentId(), 7);
                        $I->assertSame($found->getChildrenIds(), []);
                        $I->assertSame($found->getRecursiveChildrenIds(), []);
                        $I->assertSame($found->getRootParentId(), 2);
                        $I->assertSame($found->getLevel(), 4);
                        $I->assertSame($found->getBranchSiblingsIds(), []);
                        break;
                    }
                    
                }
                
            }
        }
    }
    
}