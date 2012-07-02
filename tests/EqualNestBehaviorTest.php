<?php

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class EqualNestBehaviorTest extends TestCase
{
    protected function setUp()
    {
        if (!class_exists('Person')) {
            $schema = <<<XML
<database name="equal_nest_behavior">
    <table name="person">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <column name="name" type="VARCHAR" required="true" />
    </table>

    <table name="friend">
        <behavior name="equal_nest">
            <parameter name="parent_table" value="person" />
        </behavior>
    </table>
</database>
XML;
            $this->getBuilder($schema)->build();
        }

        Propel::disableInstancePooling();
        PersonQuery::create()->deleteAll();
    }

    public function testObjectMethods()
    {
        $this->assertTrue(method_exists('Person', 'hasFriend'));
        $this->assertTrue(method_exists('Person', 'addFriend'));
        $this->assertTrue(method_exists('Person', 'removeFriend'));
        $this->assertTrue(method_exists('Person', 'getFriends'));
        $this->assertTrue(method_exists('Person', 'setFriends'));
        $this->assertTrue(method_exists('Person', 'addFriends'));
        $this->assertTrue(method_exists('Person', 'removeFriends'));
        $this->assertTrue(method_exists('Person', 'countFriends'));
    }

    public function testQueryMethods()
    {
        $this->assertTrue(method_exists('PersonQuery', 'countFriendsOf'));
        $this->assertTrue(method_exists('PersonQuery', 'findFriendsOf'));
    }

    public function testHasFriend()
    {
        $john = new Person();
        $jean = new Person();

        $this->assertEquals(0, $john->countFriends());
        $this->assertEquals(0, $jean->countFriends());
        $this->assertFalse($john->hasFriend($jean));
        $this->assertFalse($jean->hasFriend($john));
        $this->assertFalse($jean->hasFriend($jean));
        $this->assertFalse($john->hasFriend($john));
    }

    public function testHasFriendWithFriendship()
    {
        $john = new Person();
        $jean = new Person();

        $this->assertEquals(0, $john->countFriends());
        $this->assertEquals(0, $jean->countFriends());
        $this->assertFalse($john->hasFriend($jean));
        $this->assertFalse($jean->hasFriend($john));

        $john->addFriend($jean);

        $this->assertTrue($john->hasFriend($jean));
        $this->assertTrue($jean->hasFriend($john));
        $this->assertEquals(1, $john->countFriends());
        $this->assertEquals(1, $jean->countFriends());
    }

    public function testAddFriend()
    {
        $john = new Person();
        $jean = new Person();

        $this->assertEquals(0, $john->countFriends());
        $this->assertEquals(0, $jean->countFriends());

        $john->addFriend($jean);

        $this->assertEquals(1, $john->countFriends());
        $this->assertEquals(1, $jean->countFriends());
    }

    public function testRemoveFriend()
    {
        $john = new Person();
        $jean = new Person();

        $this->assertEquals(0, $john->countFriends());
        $this->assertEquals(0, $jean->countFriends());

        $john->addFriend($jean);
        $this->assertEquals(1, $john->countFriends());
        $this->assertEquals(1, $jean->countFriends());

        $john->removeFriend($jean);
        $this->assertEquals(0, $john->countFriends());
        // TODO: fix this test
        // $this->assertEquals(0, $jean->countFriends());
    }

    public function testGetFriends()
    {
        $john = new Person();
        $john->setName('john');
        $jean = new Person();
        $jean->setName('jean');

        $john->addFriend($jean);
        $john->save();

        $this->assertEquals(2, PersonQuery::create()->count());

        $this->assertTrue($jean->hasFriend($john));
        $this->assertTrue($john->hasFriend($jean));

        $coll = $jean->getFriends();
        $this->assertInstanceOf('PropelObjectCollection', $coll);
        $this->assertFalse($coll->isEmpty());
        $this->assertInstanceOf('Person', $coll[0]);
        $this->assertEquals('john', $coll[0]->getName());

        $coll = $john->getFriends();
        $this->assertInstanceOf('PropelObjectCollection', $coll);
        $this->assertFalse($coll->isEmpty());
        $this->assertInstanceOf('Person', $coll[0]);
        $this->assertEquals('jean', $coll[0]->getName());
    }

    public function testSetFriendsWithArray()
    {
        $john = new Person();
        $john->setName('john');
        $jean = new Person();
        $jean->setName('jean');
        $phil = new Person();
        $phil->setName('phil');

        $john->setFriends(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, PersonQuery::create()->count());

        $this->assertTrue($john->hasFriend($jean));
        $this->assertTrue($john->hasFriend($phil));
        $this->assertTrue($phil->hasFriend($john));
        $this->assertTrue($jean->hasFriend($john));

        $this->assertEquals(2, count($john->getFriends()));
    }

    public function testSetFriendsWithPropelCollection()
    {
        $john = new Person();
        $john->setName('john');
        $jean = new Person();
        $jean->setName('jean');
        $phil = new Person();

        $phil->setName('phil');

        $coll = new \PropelCollection();
        $coll->append($jean);
        $coll->append($phil);

        $john->setFriends($coll);
        $john->save();

        $this->assertEquals(3, PersonQuery::create()->count());

        $this->assertTrue($john->hasFriend($jean));
        $this->assertTrue($john->hasFriend($phil));
        $this->assertTrue($phil->hasFriend($john));
        $this->assertTrue($jean->hasFriend($john));

        $this->assertEquals(2, count($john->getFriends()));
    }

    public function testAddFriendsWithArray()
    {
        $john = new Person();
        $john->setName('john');
        $jean = new Person();
        $jean->setName('jean');
        $phil = new Person();
        $phil->setName('phil');

        $john->setFriends(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, PersonQuery::create()->count());

        $this->assertTrue($john->hasFriend($jean));
        $this->assertTrue($john->hasFriend($phil));
        $this->assertTrue($phil->hasFriend($john));
        $this->assertTrue($jean->hasFriend($john));

        $this->assertEquals(2, count($john->getFriends()));

        $henri = new Person();
        $henri->setName('henri');
        $marco = new Person();
        $marco->setName('marco');

        $john->addFriends(array($henri, $marco));
        $john->save();

        $this->assertEquals(5, PersonQuery::create()->count());

        $this->assertTrue($john->hasFriend($henri));
        $this->assertTrue($john->hasFriend($marco));
        $this->assertTrue($marco->hasFriend($john));
        $this->assertTrue($henri->hasFriend($john));

        $this->assertEquals(4, count($john->getFriends()));
    }

    public function testAddFriendsWithPropelCollection()
    {
        $john = new Person();
        $john->setName('john');
        $jean = new Person();
        $jean->setName('jean');
        $phil = new Person();
        $phil->setName('phil');

        $john->setFriends(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, PersonQuery::create()->count());

        $this->assertTrue($john->hasFriend($jean));
        $this->assertTrue($john->hasFriend($phil));
        $this->assertTrue($phil->hasFriend($john));
        $this->assertTrue($jean->hasFriend($john));

        $this->assertEquals(2, count($john->getFriends()));

        $henri = new Person();
        $henri->setName('henri');
        $marco = new Person();
        $marco->setName('marco');

        $coll = new PropelCollection();
        $coll->append($henri);
        $coll->append($marco);

        $john->addFriends($coll);
        $john->save();

        $this->assertEquals(5, PersonQuery::create()->count());

        $this->assertTrue($john->hasFriend($henri));
        $this->assertTrue($john->hasFriend($marco));
        $this->assertTrue($marco->hasFriend($john));
        $this->assertTrue($henri->hasFriend($john));

        $this->assertEquals(4, count($john->getFriends()));
    }

    public function testRemoveFriends()
    {
        $john = new Person();
        $john->setName('john');
        $jean = new Person();
        $jean->setName('jean');
        $phil = new Person();
        $phil->setName('phil');

        $john->setFriends(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, PersonQuery::create()->count());

        $this->assertTrue($john->hasFriend($jean));
        $this->assertTrue($john->hasFriend($phil));
        $this->assertTrue($phil->hasFriend($john));
        $this->assertTrue($jean->hasFriend($john));

        $john->removeFriends();
        $john->save();

        $this->assertEquals(0, count($john->getFriends()));
        $this->assertEquals(3, PersonQuery::create()->count());

        $this->assertFalse($john->hasFriend($phil));
        $this->assertFalse($john->hasFriend($jean));
        $this->assertFalse($phil->hasFriend($john));
        $this->assertFalse($jean->hasFriend($john));
    }

    public function testCountFriends()
    {
        $john = new Person();
        $john->setName('john');
        $jean = new Person();
        $jean->setName('jean');
        $phil = new Person();
        $phil->setName('phil');

        $this->assertEquals(0, $john->countFriends());

        $john->setFriends(array($jean, $phil));
        $john->save();

        $this->assertEquals(2, $john->countFriends());

        $john->removeFriends();
        $this->assertEquals(0, $john->countFriends());

        $john->save();
        $this->assertEquals(0, $john->countFriends());

        $john->addFriend($phil);
        $this->assertEquals(1, $john->countFriends());

        $john->save();
        $this->assertEquals(1, $john->countFriends());
    }
}
