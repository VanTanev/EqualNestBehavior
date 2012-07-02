<?php

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class EqualNestBehaviorWithCustomParametersTest extends TestCase
{
    protected function setUp()
    {
        if (!class_exists('User')) {
            $schema = <<<XML
<database name="equal_nest_behavior_with_custom_parameters">
    <table name="user">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <column name="name" type="VARCHAR" required="true" />
    </table>

    <table name="ami">
        <behavior name="equal_nest">
            <parameter name="parent_table"       value="user" />
            <parameter name="reference_column_1" value="ami_1" />
            <parameter name="reference_column_2" value="ami_2" />
        </behavior>
    </table>
</database>
XML;
            $this->getBuilder($schema)->build();
        }

        Propel::disableInstancePooling();
        UserQuery::create()->deleteAll();
    }

    public function testObjectMethods()
    {
        $this->assertTrue(method_exists('User', 'hasAmi'));
        $this->assertTrue(method_exists('User', 'addAmi'));
        $this->assertTrue(method_exists('User', 'removeAmi'));
        $this->assertTrue(method_exists('User', 'getAmis'));
        $this->assertTrue(method_exists('User', 'setAmis'));
        $this->assertTrue(method_exists('User', 'addAmis'));
        $this->assertTrue(method_exists('User', 'removeAmis'));
        $this->assertTrue(method_exists('User', 'countAmis'));
    }

    public function testQueryMethods()
    {
        $this->assertTrue(method_exists('UserQuery', 'countAmisOf'));
        $this->assertTrue(method_exists('UserQuery', 'findAmisOf'));
    }

    public function testHasAmi()
    {
        $john = new User();
        $jean = new User();

        $this->assertEquals(0, $john->countAmis());
        $this->assertEquals(0, $jean->countAmis());
        $this->assertFalse($john->hasAmi($jean));
        $this->assertFalse($jean->hasAmi($john));
        $this->assertFalse($jean->hasAmi($jean));
        $this->assertFalse($john->hasAmi($john));
    }

    public function testHasAmiWithAmiship()
    {
        $john = new User();
        $jean = new User();

        $this->assertEquals(0, $john->countAmis());
        $this->assertEquals(0, $jean->countAmis());
        $this->assertFalse($john->hasAmi($jean));
        $this->assertFalse($jean->hasAmi($john));

        $john->addAmi($jean);

        $this->assertTrue($john->hasAmi($jean));
        $this->assertTrue($jean->hasAmi($john));
        $this->assertEquals(1, $john->countAmis());
        $this->assertEquals(1, $jean->countAmis());
    }

    public function testAddAmi()
    {
        $john = new User();
        $jean = new User();

        $this->assertEquals(0, $john->countAmis());
        $this->assertEquals(0, $jean->countAmis());

        $john->addAmi($jean);

        $this->assertEquals(1, $john->countAmis());
        $this->assertEquals(1, $jean->countAmis());
    }

    public function testRemoveAmi()
    {
        $john = new User();
        $jean = new User();

        $this->assertEquals(0, $john->countAmis());
        $this->assertEquals(0, $jean->countAmis());

        $john->addAmi($jean);
        $this->assertEquals(1, $john->countAmis());
        $this->assertEquals(1, $jean->countAmis());

        $john->removeAmi($jean);
        $this->assertEquals(0, $john->countAmis());
        $this->assertEquals(0, $jean->countAmis());
    }

    public function testGetAmis()
    {
        $john = new User();
        $john->setName('john');
        $jean = new User();
        $jean->setName('jean');

        $john->addAmi($jean);
        $john->save();

        $this->assertEquals(2, UserQuery::create()->count());

        $this->assertTrue($jean->hasAmi($john));
        $this->assertTrue($john->hasAmi($jean));

        $coll = $jean->getAmis();
        $this->assertInstanceOf('PropelObjectCollection', $coll);
        $this->assertFalse($coll->isEmpty());
        $this->assertInstanceOf('User', $coll[0]);
        $this->assertEquals('john', $coll[0]->getName());

        $coll = $john->getAmis();
        $this->assertInstanceOf('PropelObjectCollection', $coll);
        $this->assertFalse($coll->isEmpty());
        $this->assertInstanceOf('User', $coll[0]);
        $this->assertEquals('jean', $coll[0]->getName());
    }

    public function testSetAmisWithArray()
    {
        $john = new User();
        $john->setName('john');
        $jean = new User();
        $jean->setName('jean');
        $phil = new User();
        $phil->setName('phil');

        $john->setAmis(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, UserQuery::create()->count());

        $this->assertTrue($john->hasAmi($jean));
        $this->assertTrue($john->hasAmi($phil));
        $this->assertTrue($phil->hasAmi($john));
        $this->assertTrue($jean->hasAmi($john));

        $this->assertEquals(2, count($john->getAmis()));
    }

    public function testSetAmisWithPropelCollection()
    {
        $john = new User();
        $john->setName('john');
        $jean = new User();
        $jean->setName('jean');
        $phil = new User();

        $phil->setName('phil');

        $coll = new \PropelCollection();
        $coll->append($jean);
        $coll->append($phil);

        $john->setAmis($coll);
        $john->save();

        $this->assertEquals(3, UserQuery::create()->count());

        $this->assertTrue($john->hasAmi($jean));
        $this->assertTrue($john->hasAmi($phil));
        $this->assertTrue($phil->hasAmi($john));
        $this->assertTrue($jean->hasAmi($john));

        $this->assertEquals(2, count($john->getAmis()));
    }

    public function testAddAmisWithArray()
    {
        $john = new User();
        $john->setName('john');
        $jean = new User();
        $jean->setName('jean');
        $phil = new User();
        $phil->setName('phil');

        $john->setAmis(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, UserQuery::create()->count());

        $this->assertTrue($john->hasAmi($jean));
        $this->assertTrue($john->hasAmi($phil));
        $this->assertTrue($phil->hasAmi($john));
        $this->assertTrue($jean->hasAmi($john));

        $this->assertEquals(2, count($john->getAmis()));

        $henri = new User();
        $henri->setName('henri');
        $marco = new User();
        $marco->setName('marco');

        $john->addAmis(array($henri, $marco));
        $john->save();

        $this->assertEquals(5, UserQuery::create()->count());

        $this->assertTrue($john->hasAmi($henri));
        $this->assertTrue($john->hasAmi($marco));
        $this->assertTrue($marco->hasAmi($john));
        $this->assertTrue($henri->hasAmi($john));

        $this->assertEquals(4, count($john->getAmis()));
    }

    public function testAddAmisWithPropelCollection()
    {
        $john = new User();
        $john->setName('john');
        $jean = new User();
        $jean->setName('jean');
        $phil = new User();
        $phil->setName('phil');

        $john->setAmis(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, UserQuery::create()->count());

        $this->assertTrue($john->hasAmi($jean));
        $this->assertTrue($john->hasAmi($phil));
        $this->assertTrue($phil->hasAmi($john));
        $this->assertTrue($jean->hasAmi($john));

        $this->assertEquals(2, count($john->getAmis()));

        $henri = new User();
        $henri->setName('henri');
        $marco = new User();
        $marco->setName('marco');

        $coll = new PropelCollection();
        $coll->append($henri);
        $coll->append($marco);

        $john->addAmis($coll);
        $john->save();

        $this->assertEquals(5, UserQuery::create()->count());

        $this->assertTrue($john->hasAmi($henri));
        $this->assertTrue($john->hasAmi($marco));
        $this->assertTrue($marco->hasAmi($john));
        $this->assertTrue($henri->hasAmi($john));

        $this->assertEquals(4, count($john->getAmis()));
    }

    public function testRemoveAmis()
    {
        $john = new User();
        $john->setName('john');
        $jean = new User();
        $jean->setName('jean');
        $phil = new User();
        $phil->setName('phil');

        $john->setAmis(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, UserQuery::create()->count());

        $this->assertTrue($john->hasAmi($jean));
        $this->assertTrue($john->hasAmi($phil));
        $this->assertTrue($phil->hasAmi($john));
        $this->assertTrue($jean->hasAmi($john));

        $john->removeAmis();

        $this->assertEquals(0, count($john->getAmis()));
        $this->assertEquals(3, UserQuery::create()->count());

        $this->assertFalse($john->hasAmi($phil));
        $this->assertFalse($john->hasAmi($jean));
        $this->assertFalse($phil->hasAmi($john));
        $this->assertFalse($jean->hasAmi($john));

        $john->save();

        $this->assertEquals(3, UserQuery::create()->count());
        $this->assertEquals(0, AmiQuery::create()->count());
        $this->assertEquals(0, count($john->getAmis()));

        $this->assertFalse($john->hasAmi($phil));
        $this->assertFalse($john->hasAmi($jean));
        $this->assertFalse($phil->hasAmi($john));
        $this->assertFalse($jean->hasAmi($john));
    }

    public function testCountAmis()
    {
        $john = new User();
        $john->setName('john');
        $jean = new User();
        $jean->setName('jean');
        $phil = new User();
        $phil->setName('phil');

        $this->assertEquals(0, $john->countAmis());

        $john->setAmis(array($jean, $phil));
        $john->save();

        $this->assertEquals(2, $john->countAmis());

        $john->removeAmis();
        $this->assertEquals(0, $john->countAmis());

        $john->save();
        $this->assertEquals(0, $john->countAmis());

        $john->addAmi($phil);
        $this->assertEquals(1, $john->countAmis());

        $john->save();
        $this->assertEquals(1, $john->countAmis());
    }

    public function testCountAmisOf()
    {
        $john = new User();
        $john->setName('john');
        $john->save();

        $jean = new User();
        $jean->setName('jean');
        $jean->save();

        $phil = new User();
        $phil->setName('phil');
        $phil->save();

        $this->assertEquals(0, AmiQuery::create()->count());

        $this->assertEquals(0, UserQuery::create()->countAmisOf($phil));
        $this->assertEquals(0, UserQuery::create()->countAmisOf($jean));
        $this->assertEquals(0, UserQuery::create()->countAmisOf($john));

        $jean->addAmi($phil);

        $this->assertEquals(0, AmiQuery::create()->count());
        $this->assertEquals(1, UserQuery::create()->countAmisOf($phil));
        $this->assertEquals(1, UserQuery::create()->countAmisOf($jean));

        $jean->save();
        $this->assertEquals(1, UserQuery::create()->countAmisOf($phil));
        $this->assertEquals(1, UserQuery::create()->countAmisOf($jean));

        $this->assertEquals(1, AmiQuery::create()->count());

        $jean->removeAmis();
        $jean->save();

        $this->assertEquals(0, UserQuery::create()->countAmisOf($jean));
        $this->assertEquals(0, UserQuery::create()->countAmisOf($phil));
    }

    public function testFindAmisOf()
    {
        $john = new User();
        $john->setName('john');
        $john->save();

        $jean = new User();
        $jean->setName('jean');
        $jean->save();

        $phil = new User();
        $phil->setName('phil');
        $phil->save();

        $this->assertEquals(0, UserQuery::create()->findAmisOf($phil)->count());
        $this->assertEquals(0, UserQuery::create()->findAmisOf($jean)->count());
        $this->assertEquals(0, UserQuery::create()->findAmisOf($john)->count());

        $jean->addAmi($phil);
        $this->assertEquals(0, UserQuery::create()->findAmisOf($phil)->count());
        $this->assertEquals(0, UserQuery::create()->findAmisOf($jean)->count());

        $jean->save();
        $this->assertEquals(1, UserQuery::create()->findAmisOf($phil)->count());
        $this->assertEquals(1, UserQuery::create()->findAmisOf($jean)->count());

        $coll = UserQuery::create()->findAmisOf($phil);
        $this->assertInstanceOf('PropelObjectCollection', $coll);
        $this->assertInstanceOf('User', $coll[0]);
        $this->assertEquals('jean', $coll[0]->getName());

        $coll = UserQuery::create()->findAmisOf($jean);
        $this->assertInstanceOf('PropelObjectCollection', $coll);
        $this->assertInstanceOf('User', $coll[0]);
        $this->assertEquals('phil', $coll[0]->getName());

        $jean->removeAmis();
        $jean->save();

        $this->assertEquals(0, AmiQuery::create()->count());

        $this->assertEquals(0, UserQuery::create()->findAmisOf($phil)->count());
        $this->assertEquals(0, UserQuery::create()->findAmisOf($jean)->count());
    }
}
