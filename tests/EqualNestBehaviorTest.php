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
}
