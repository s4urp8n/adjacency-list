<?php

class AdjacencyTest extends PHPUnit\Framework\TestCase
{
    use \Zver\Package\Helper;

    public function testException()
    {
        $this->expectException('\Exception');
        \Zver\AdjacencyList::load([['str']]);
    }

    public function testEmpty()
    {
        $adjacencyList = \Zver\AdjacencyList::load([]);

        $this->assertFalse($adjacencyList->find(1));
        $this->assertFalse($adjacencyList->find(2));
        $this->assertFalse($adjacencyList->find(3));
        $this->assertFalse($adjacencyList->find(9));
        $this->assertFalse($adjacencyList->find(100));

        $this->assertSame($adjacencyList->getMaxLevel(), 0);
        $this->assertSame($adjacencyList->getList(), []);
        $this->assertSame($adjacencyList->getItems(), []);
        $this->assertTrue($adjacencyList->isEmpty());

        $adjacencyList->walk(
            function () {

            }
        );

    }

    public function testLoad()
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

        foreach ([$testListArray, $testListClassFromInterface] as $testedList) {

            $adjacencyList = \Zver\AdjacencyList::load($testedList);

            $this->assertFalse($adjacencyList->isEmpty());
            $this->assertSame($adjacencyList->getMaxLevel(), 4);

            $this->assertNotEmpty($adjacencyList->getItems());
            $this->assertNotEmpty($adjacencyList->getList());

            foreach ($testListArray as $originArray) {
                /** @var \Zver\AdjacencyListItem $found */

                $found = $adjacencyList->find($originArray['id']);

                $this->assertFalse($adjacencyList->find($unexistedId));

                $this->assertNotEmpty($found);
                $this->assertSame($found->getId(), $originArray['id']);
                $this->assertSame($found . '', (string)$originArray['id']);
                $this->assertSame($found->getData(), $originArray['data']);
                $this->assertInstanceOf('\Zver\AdjacencyListItem', $found);

                $haveParent = $found->haveParent();

                if (empty($originArray['parent'])) {
                    $this->assertFalse($haveParent);
                } else {
                    $this->assertTrue($haveParent);

                    /** @var \Zver\AdjacencyListItem $parent */

                    $parent = $adjacencyList->find($found->getParentId());

                    $this->assertNotEmpty($parent);
                    $this->assertSame($parent->getId(), $originArray['parent']);
                    $this->assertInstanceOf('\Zver\AdjacencyListItem', $parent);
                    $this->assertTrue($parent->haveChildren());
                    $this->assertFalse($parent->haveChildren($unexistedId));
                    $this->assertTrue($parent->haveChildren($found->getId()));
                }

                switch ($found->getId()) {

                    case 1:
                        {
                            $this->assertSame($found->getId(), 1);
                            $this->assertSame($found->getData(), 'data1');
                            $this->assertSame($found->getParentId(), null);
                            $this->assertSame($found->getChildrenIds(), [3]);
                            $this->assertSame($found->getRecursiveChildrenIds(), [3]);
                            $this->assertSame($found->getRootParentId(), 1);
                            $this->assertSame($found->getLevel(), 1);
                            $this->assertSame($found->getBranchSiblingsIds(), []);
                            break;
                        }
                    case 2:
                        {
                            $this->assertSame($found->getId(), 2);
                            $this->assertSame($found->getData(), 'data2');
                            $this->assertSame($found->getParentId(), null);
                            $this->assertSame($found->getChildrenIds(), [4, 5]);
                            $this->assertSame($found->getRecursiveChildrenIds(), [4, 5, 7, 8]);
                            $this->assertSame($found->getRootParentId(), 2);
                            $this->assertSame($found->getLevel(), 1);
                            $this->assertSame($found->getBranchSiblingsIds(), []);
                            break;
                        }
                    case 3:
                        {
                            $this->assertSame($found->getId(), 3);
                            $this->assertSame($found->getData(), 'data3');
                            $this->assertSame($found->getParentId(), 1);
                            $this->assertSame($found->getChildrenIds(), []);
                            $this->assertSame($found->getRecursiveChildrenIds(), []);
                            $this->assertSame($found->getRootParentId(), 1);
                            $this->assertSame($found->getLevel(), 2);
                            $this->assertSame($found->getBranchSiblingsIds(), []);
                            break;
                        }
                    case 4:
                        {
                            $this->assertSame($found->getId(), 4);
                            $this->assertSame($found->getData(), 'data4');
                            $this->assertSame($found->getParentId(), 2);
                            $this->assertSame($found->getChildrenIds(), []);
                            $this->assertSame($found->getRecursiveChildrenIds(), []);
                            $this->assertSame($found->getRootParentId(), 2);
                            $this->assertSame($found->getLevel(), 2);
                            $this->assertSame($found->getBranchSiblingsIds(), [5]);
                            break;
                        }
                    case 5:
                        {
                            $this->assertSame($found->getId(), 5);
                            $this->assertSame($found->getData(), 'data5');
                            $this->assertSame($found->getParentId(), 2);
                            $this->assertSame($found->getChildrenIds(), [7]);
                            $this->assertSame($found->getRecursiveChildrenIds(), [7, 8]);
                            $this->assertSame($found->getRootParentId(), 2);
                            $this->assertSame($found->getLevel(), 2);
                            $this->assertSame($found->getBranchSiblingsIds(), [4]);
                            break;
                        }
                    case 6:
                        {
                            $this->assertSame($found->getId(), 6);
                            $this->assertSame($found->getData(), 'data6');
                            $this->assertSame($found->getParentId(), null);
                            $this->assertSame($found->getChildrenIds(), []);
                            $this->assertSame($found->getRecursiveChildrenIds(), []);
                            $this->assertSame($found->getRootParentId(), 6);
                            $this->assertSame($found->getLevel(), 1);
                            $this->assertSame($found->getBranchSiblingsIds(), []);
                            break;
                        }
                    case 7:
                        {
                            $this->assertSame($found->getId(), 7);
                            $this->assertSame($found->getData(), 'data7');
                            $this->assertSame($found->getParentId(), 5);
                            $this->assertSame($found->getChildrenIds(), [8]);
                            $this->assertSame($found->getRecursiveChildrenIds(), [8]);
                            $this->assertSame($found->getRootParentId(), 2);
                            $this->assertSame($found->getLevel(), 3);
                            $this->assertSame($found->getBranchSiblingsIds(), []);
                            break;
                        }
                    case 8:
                        {
                            $this->assertSame($found->getId(), 8);
                            $this->assertSame($found->getData(), 'data8');
                            $this->assertSame($found->getParentId(), 7);
                            $this->assertSame($found->getChildrenIds(), []);
                            $this->assertSame($found->getRecursiveChildrenIds(), []);
                            $this->assertSame($found->getRootParentId(), 2);
                            $this->assertSame($found->getLevel(), 4);
                            $this->assertSame($found->getBranchSiblingsIds(), []);
                            break;
                        }

                }

            }
        }
    }

    public function getTestRelations()
    {

        $obj = new stdClass();

        $obj->p = 6;

        return [
            [
                'id'     => 1,
                'parent' => null,
                'data'   => ['p' => 1],
            ],
            [
                'id'     => 2,
                'parent' => null,
                'data'   => ['p' => 2],
            ],
            [
                'id'     => 3,
                'parent' => null,
                'data'   => ['p' => 3],
            ],
            [
                'id'     => 4,
                'parent' => 3,
                'data'   => ['p' => 4],
            ],
            [
                'id'     => 5,
                'parent' => 4,
                'data'   => ['p' => 5],
            ],
            [
                'id'     => 6,
                'parent' => 5,
                'data'   => $obj,
            ],
        ];

    }

    public function testDataProperty()
    {
        $list = \Zver\AdjacencyList::load($this->getTestRelations());

        for ($i = 1; $i <= 6; $i++) {
            $this->assertSame($list->find($i)
                                   ->getDataProperty('p'), $i);
        }
    }

    public function testRecursiveDataProperty()
    {
        $list = \Zver\AdjacencyList::load($this->getTestRelations());

        $this->assertSame(
            $list->find(6)
                 ->getRecursiveDataProperty('p'),
            [
                3,
                4,
                5,
                6,
            ]
        );

        for ($i = 1; $i <= 3; $i++) {

            $this->assertSame(
                $list->find($i)
                     ->getRecursiveDataProperty('p'),
                [
                    $i,
                ]
            );
        }
    }

    public function testRecursiveParentIds()
    {
        $list = \Zver\AdjacencyList::load($this->getTestRelations());

        $this->assertSame($list->find(6)
                               ->getRecursiveParentIds(), [5, 4, 3]);

        $this->assertSame($list->find(3)
                               ->getRecursiveParentIds(), []);

        $this->assertSame($list->find(4)
                               ->getRecursiveParentIds(), [3]);

    }

    public function testIsParent()
    {
        $list = \Zver\AdjacencyList::load($this->getTestRelations());

        $this->assertTrue($list->find(6)
                               ->haveParentId(5));

        $this->assertTrue($list->find(6)
                               ->haveParentId(4));

        $this->assertTrue($list->find(6)
                               ->haveParentId(3));

        $this->assertFalse($list->find(3)
                                ->haveParentId(5));

        $this->assertTrue($list->find(5)
                               ->haveParentId(4));

        $this->assertTrue($list->find(5)
                               ->haveParentId(3));

    }

}