<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Util
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Util;


use YapepBaseTest\Mock\Util\CollectionElementMock;
use YapepBaseTest\Mock\Util\CollectionMock;

/**
 * Test class for Collection.
 * Generated by PHPUnit on 2012-01-31 at 12:10:44.
 */
class CollectionTest extends \YapepBaseTest\TestAbstract {
	public function testConstructor() {
		$o1 = new CollectionElementMock();
		$o2 = new CollectionElementMock();
		$elements = array();
		$elements[] = $o1;
		$elements[] = $o2;
		$collection = new CollectionMock($elements);
		$this->assertEquals($o1, $collection[0]);
		$this->assertEquals($o2, $collection[1]);

		$o3 = new CollectionElementMock();
		$collection = new CollectionMock($o3);
		$this->assertEquals($o3, $collection[0]);
	}

	public function testOffset() {
		$collection = new CollectionMock();
		$this->assertEquals(0, $collection->count());
		try {
			$collection->offsetGet(0);
			$this->fail('offsetGet on a non existent element in a Collection should result in an IndexOutOfBoundsException');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) { }
		$o = new CollectionElementMock();
		$collection->offsetSet(0, $o);
		$this->assertEquals(1, $collection->count());
		$this->assertEquals($o, $collection->offsetGet(0));
		$this->assertEquals(true, $collection->offsetExists(0));
		$collection->offsetUnset(0);
		$this->assertEquals(0, $collection->count());
		try {
			$collection->offsetGet(0);
			$this->fail('offsetGet on a non existent element in a Collection should result in an IndexOutOfBoundsException');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) { }

		$collection->offsetSet(null, $o);
		$this->assertEquals(1, $collection->count());
		$this->assertEquals($o, $collection->offsetGet(1));
		$this->assertEquals(true, $collection->offsetExists(1));
		$collection->offsetUnset(1);
		$this->assertEquals(0, $collection->count());
		try {
			$collection->offsetGet(0);
			$this->fail('offsetGet on a non existent element in a Collection should result in an IndexOutOfBoundsException');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) { }

		try {
			$collection->offsetUnset(0);
			$this->fail('offsetUnset on a non existent element in a Collection should result in an IndexOutOfBoundsException');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) { }
	}

	public function testKeyCheck() {
		$this->setExpectedException('\YapepBase\Exception\ValueException');
		$collection = new CollectionMock();
		$o = new CollectionElementMock();
		$collection->offsetSet('test', $o);
		$this->fail('Collection::keyCheck should throw a ValueException, if a string offset is passed');
	}

	public function testIterator() {
		$collection = new CollectionMock();
		$this->assertEquals(false, $collection->rewind());
		$o1 = new CollectionElementMock();
		$o2 = new CollectionElementMock();
		$o3 = new CollectionElementMock();
		$collection[] = $o1;
		$collection[] = $o2;
		$collection[] = $o3;
		$this->assertEquals($o1, $collection->current());
		$this->assertEquals(0, $collection->key());
		$this->assertEquals(true, $collection->valid());
		$this->assertEquals($o1, $collection->next());

		$this->assertEquals($o2, $collection->current());
		$this->assertEquals(1, $collection->key());
		$this->assertEquals(true, $collection->valid());
		$this->assertEquals($o2, $collection->next());

		$this->assertEquals($o3, $collection->current());
		$this->assertEquals(2, $collection->key());
		$this->assertEquals(true, $collection->valid());
		$this->assertEquals($o3, $collection->next());

		$this->assertEquals(false, $collection->valid());
		try {
			$collection->current();
			$this->fail('Calling Collection::current() at the end of the collection should result in an IndexOutOfBoundsException');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) { }
		try {
			$collection->next();
			$this->fail('Calling Collection::next() at the end of the collection should result in an IndexOutOfBoundsException');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) { }

		$this->assertEquals(true, $collection->rewind());
		$this->assertEquals($o1, $collection->current());
		$this->assertEquals(0, $collection->key());
		$this->assertEquals(true, $collection->valid());
	}

	public function testSerialize() {
		$collection = new CollectionMock();
		$o = new CollectionElementMock();
		$collection[] = $o;
		$this->assertEquals($collection, unserialize(serialize($collection)));
	}

	public function testExtendedApi() {
		$collection1 = new CollectionMock();
		$o1 = new CollectionElementMock();
		$o2 = new CollectionElementMock();
		$o3 = new CollectionElementMock();
		$collection1->add($o1);
		$this->assertEquals($o1, $collection1[0]);
		$collection1->add($o2);
		$this->assertEquals($o2, $collection1[1]);
		$collection1->clear();
		$this->assertEquals(0, $collection1->count());

		$collection1->add($o1);
		$collection1->add($o2);
		$collection2 = new CollectionMock();
		$collection2->addAll($collection1);
		$this->assertEquals($o1, $collection2[0]);
		$this->assertEquals($o2, $collection2[1]);

		$this->assertEquals(true, $collection2->contains($o2));
		$this->assertEquals(false, $collection2->contains($o3));

		$collection1->clear();
		$collection1->add($o1);
		$collection1->add($o2);
		$collection2->clear();
		$collection2->add($o1);
		$collection2->add($o2);
		$collection3 = new CollectionMock();
		$collection3->add($o3);
		$this->assertEquals(true, $collection2->containsAll($collection1));
		$this->assertEquals(false, $collection2->containsAll($collection3));

		$collection2->clear();
		$collection2->add($o1);
		$collection2->add($o2);
		$this->assertEquals(true, $collection2->remove($o1));
		$this->assertEquals(false, $collection2->remove($o1));
		$this->assertEquals(false, $collection2->contains($o1));
		$this->assertEquals(true, $collection2->contains($o2));

		$collection2->clear();
		$collection2->add($o1);
		$collection2->add($o2);
		$collection3->clear();
		$collection3->add($o1);
		$collection2->removeAll($collection3);
		$this->assertEquals(false, $collection2->contains($o1));
		$this->assertEquals(true, $collection2->contains($o2));
		$this->assertEquals(false, $collection2->contains($o3));

		$collection2->clear();
		$collection2->add($o1);
		$collection2->add($o2);
		$collection2->add($o3);
		$collection3->clear();
		$collection3->add($o1);
		$collection3->add($o3);
		$collection2->retainAll($collection3);
		$this->assertEquals(true, $collection2->contains($o1));
		$this->assertEquals(false, $collection2->contains($o2));
		$this->assertEquals(true, $collection2->contains($o3));
	}
}
