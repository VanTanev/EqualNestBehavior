<?php

/**
 * @author William Durand <william.durand1@gmail.com>
 * @author Ivan Tanev <van.tanev@gmail.com>
 */
class EqualNestBehaviorWithClassPrefixTest extends TestCase
{
    protected function setUp()
    {
        if (!class_exists('DBStudent')) {
            $schema = <<<XML
<database name="class_prefixed_db">
    <table name="student">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <column name="name" type="VARCHAR" required="true" />
    </table>

    <table name="classmate">
        <behavior name="equal_nest">
            <parameter name="parent_table" value="student" />
        </behavior>
    </table>
</database>
XML;
            $builder = $this->getBuilder($schema);

            $config = $builder->getConfig();
            $config->setBuildProperty('classPrefix', 'DB');
            $builder->setConfig($config);

            $builder->build();
        }

        Propel::disableInstancePooling();
        DBStudentQuery::create()->deleteAll();
    }

    public function testObjectMethods()
    {
        $this->assertTrue(method_exists('DBStudent', 'hasClassmate'));
        $this->assertTrue(method_exists('DBStudent', 'addClassmate'));
        $this->assertTrue(method_exists('DBStudent', 'removeClassmate'));
        $this->assertTrue(method_exists('DBStudent', 'getClassmates'));
        $this->assertTrue(method_exists('DBStudent', 'setClassmates'));
        $this->assertTrue(method_exists('DBStudent', 'addClassmates'));
        $this->assertTrue(method_exists('DBStudent', 'removeClassmates'));
        $this->assertTrue(method_exists('DBStudent', 'countClassmates'));
    }

    public function testQueryMethods()
    {
        $this->assertTrue(method_exists('DBStudentQuery', 'countClassmatesOf'));
        $this->assertTrue(method_exists('DBStudentQuery', 'findClassmatesOf'));
    }

    public function testHasClassmate()
    {
        $john = new DBStudent();
        $jean = new DBStudent();

        $this->assertEquals(0, $john->countClassmates());
        $this->assertEquals(0, $jean->countClassmates());
        $this->assertFalse($john->hasClassmate($jean));
        $this->assertFalse($jean->hasClassmate($john));
        $this->assertFalse($jean->hasClassmate($jean));
        $this->assertFalse($john->hasClassmate($john));
    }

    public function testHasClassmateWithClassmateship()
    {
        $john = new DBStudent();
        $jean = new DBStudent();

        $this->assertEquals(0, $john->countClassmates());
        $this->assertEquals(0, $jean->countClassmates());
        $this->assertFalse($john->hasClassmate($jean));
        $this->assertFalse($jean->hasClassmate($john));

        $john->addClassmate($jean);

        $this->assertTrue($john->hasClassmate($jean));
        $this->assertTrue($jean->hasClassmate($john));
        $this->assertEquals(1, $john->countClassmates());
        $this->assertEquals(1, $jean->countClassmates());
    }

    public function testAddClassmate()
    {
        $john = new DBStudent();
        $jean = new DBStudent();

        $this->assertEquals(0, $john->countClassmates());
        $this->assertEquals(0, $jean->countClassmates());

        $john->addClassmate($jean);

        $this->assertEquals(1, $john->countClassmates());
        $this->assertEquals(1, $jean->countClassmates());
    }

    public function testRemoveClassmate()
    {
        $john = new DBStudent();
        $jean = new DBStudent();

        $this->assertEquals(0, $john->countClassmates());
        $this->assertEquals(0, $jean->countClassmates());

        $john->addClassmate($jean);
        $this->assertEquals(1, $john->countClassmates());
        $this->assertEquals(1, $jean->countClassmates());

        $john->removeClassmate($jean);
        $this->assertEquals(0, $john->countClassmates());
        $this->assertEquals(0, $jean->countClassmates());
    }

    public function testGetClassmates()
    {
        $john = new DBStudent();
        $john->setName('john');
        $jean = new DBStudent();
        $jean->setName('jean');

        $john->addClassmate($jean);
        $john->save();

        $this->assertEquals(2, DBStudentQuery::create()->count());

        $this->assertTrue($jean->hasClassmate($john));
        $this->assertTrue($john->hasClassmate($jean));

        $coll = $jean->getClassmates();
        $this->assertInstanceOf('PropelObjectCollection', $coll);
        $this->assertFalse($coll->isEmpty());
        $this->assertInstanceOf('DBStudent', $coll[0]);
        $this->assertEquals('john', $coll[0]->getName());

        $coll = $john->getClassmates();
        $this->assertInstanceOf('PropelObjectCollection', $coll);
        $this->assertFalse($coll->isEmpty());
        $this->assertInstanceOf('DBStudent', $coll[0]);
        $this->assertEquals('jean', $coll[0]->getName());
    }

    public function testSetClassmatesWithArray()
    {
        $john = new DBStudent();
        $john->setName('john');
        $jean = new DBStudent();
        $jean->setName('jean');
        $phil = new DBStudent();
        $phil->setName('phil');

        $john->setClassmates(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, DBStudentQuery::create()->count());

        $this->assertTrue($john->hasClassmate($jean));
        $this->assertTrue($john->hasClassmate($phil));
        $this->assertTrue($phil->hasClassmate($john));
        $this->assertTrue($jean->hasClassmate($john));

        $this->assertEquals(2, count($john->getClassmates()));
    }

    public function testSetClassmatesWithPropelCollection()
    {
        $john = new DBStudent();
        $john->setName('john');
        $jean = new DBStudent();
        $jean->setName('jean');
        $phil = new DBStudent();

        $phil->setName('phil');

        $coll = new \PropelCollection();
        $coll->append($jean);
        $coll->append($phil);

        $john->setClassmates($coll);
        $john->save();

        $this->assertEquals(3, DBStudentQuery::create()->count());

        $this->assertTrue($john->hasClassmate($jean));
        $this->assertTrue($john->hasClassmate($phil));
        $this->assertTrue($phil->hasClassmate($john));
        $this->assertTrue($jean->hasClassmate($john));

        $this->assertEquals(2, count($john->getClassmates()));
    }

    public function testAddClassmatesWithArray()
    {
        $john = new DBStudent();
        $john->setName('john');
        $jean = new DBStudent();
        $jean->setName('jean');
        $phil = new DBStudent();
        $phil->setName('phil');

        $john->setClassmates(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, DBStudentQuery::create()->count());

        $this->assertTrue($john->hasClassmate($jean));
        $this->assertTrue($john->hasClassmate($phil));
        $this->assertTrue($phil->hasClassmate($john));
        $this->assertTrue($jean->hasClassmate($john));

        $this->assertEquals(2, count($john->getClassmates()));

        $henri = new DBStudent();
        $henri->setName('henri');
        $marco = new DBStudent();
        $marco->setName('marco');

        $john->addClassmates(array($henri, $marco));
        $john->save();

        $this->assertEquals(5, DBStudentQuery::create()->count());

        $this->assertTrue($john->hasClassmate($henri));
        $this->assertTrue($john->hasClassmate($marco));
        $this->assertTrue($marco->hasClassmate($john));
        $this->assertTrue($henri->hasClassmate($john));

        $this->assertEquals(4, count($john->getClassmates()));
    }

    public function testAddClassmatesWithPropelCollection()
    {
        $john = new DBStudent();
        $john->setName('john');
        $jean = new DBStudent();
        $jean->setName('jean');
        $phil = new DBStudent();
        $phil->setName('phil');

        $john->setClassmates(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, DBStudentQuery::create()->count());

        $this->assertTrue($john->hasClassmate($jean));
        $this->assertTrue($john->hasClassmate($phil));
        $this->assertTrue($phil->hasClassmate($john));
        $this->assertTrue($jean->hasClassmate($john));

        $this->assertEquals(2, count($john->getClassmates()));

        $henri = new DBStudent();
        $henri->setName('henri');
        $marco = new DBStudent();
        $marco->setName('marco');

        $coll = new PropelCollection();
        $coll->append($henri);
        $coll->append($marco);

        $john->addClassmates($coll);
        $john->save();

        $this->assertEquals(5, DBStudentQuery::create()->count());

        $this->assertTrue($john->hasClassmate($henri));
        $this->assertTrue($john->hasClassmate($marco));
        $this->assertTrue($marco->hasClassmate($john));
        $this->assertTrue($henri->hasClassmate($john));

        $this->assertEquals(4, count($john->getClassmates()));
    }

    public function testRemoveClassmates()
    {
        $john = new DBStudent();
        $john->setName('john');
        $jean = new DBStudent();
        $jean->setName('jean');
        $phil = new DBStudent();
        $phil->setName('phil');

        $john->setClassmates(array($jean, $phil));
        $john->save();

        $this->assertEquals(3, DBStudentQuery::create()->count());

        $this->assertTrue($john->hasClassmate($jean));
        $this->assertTrue($john->hasClassmate($phil));
        $this->assertTrue($phil->hasClassmate($john));
        $this->assertTrue($jean->hasClassmate($john));

        $john->removeClassmates();

        $this->assertEquals(0, count($john->getClassmates()));
        $this->assertEquals(3, DBStudentQuery::create()->count());

        $this->assertFalse($john->hasClassmate($phil));
        $this->assertFalse($john->hasClassmate($jean));
        $this->assertFalse($phil->hasClassmate($john));
        $this->assertFalse($jean->hasClassmate($john));

        $john->save();

        $this->assertEquals(3, DBStudentQuery::create()->count());
        $this->assertEquals(0, DBClassmateQuery::create()->count());
        $this->assertEquals(0, count($john->getClassmates()));

        $this->assertFalse($john->hasClassmate($phil));
        $this->assertFalse($john->hasClassmate($jean));
        $this->assertFalse($phil->hasClassmate($john));
        $this->assertFalse($jean->hasClassmate($john));
    }

    public function testCountClassmates()
    {
        $john = new DBStudent();
        $john->setName('john');
        $jean = new DBStudent();
        $jean->setName('jean');
        $phil = new DBStudent();
        $phil->setName('phil');

        $this->assertEquals(0, $john->countClassmates());

        $john->setClassmates(array($jean, $phil));
        $john->save();

        $this->assertEquals(2, $john->countClassmates());

        $john->removeClassmates();
        $this->assertEquals(0, $john->countClassmates());

        $john->save();
        $this->assertEquals(0, $john->countClassmates());

        $john->addClassmate($phil);
        $this->assertEquals(1, $john->countClassmates());

        $john->save();
        $this->assertEquals(1, $john->countClassmates());
    }

    public function testCountClassmatesOf()
    {
        $john = new DBStudent();
        $john->setName('john');
        $john->save();

        $jean = new DBStudent();
        $jean->setName('jean');
        $jean->save();

        $phil = new DBStudent();
        $phil->setName('phil');
        $phil->save();

        $this->assertEquals(0, DBClassmateQuery::create()->count());

        $this->assertEquals(0, DBStudentQuery::create()->countClassmatesOf($phil));
        $this->assertEquals(0, DBStudentQuery::create()->countClassmatesOf($jean));
        $this->assertEquals(0, DBStudentQuery::create()->countClassmatesOf($john));

        $jean->addClassmate($phil);

        $this->assertEquals(0, DBClassmateQuery::create()->count());
        $this->assertEquals(1, DBStudentQuery::create()->countClassmatesOf($phil));
        $this->assertEquals(1, DBStudentQuery::create()->countClassmatesOf($jean));

        $jean->save();
        $this->assertEquals(1, DBStudentQuery::create()->countClassmatesOf($phil));
        $this->assertEquals(1, DBStudentQuery::create()->countClassmatesOf($jean));

        $this->assertEquals(1, DBClassmateQuery::create()->count());

        $jean->removeClassmates();
        $jean->save();

        $this->assertEquals(0, DBStudentQuery::create()->countClassmatesOf($jean));
        $this->assertEquals(0, DBStudentQuery::create()->countClassmatesOf($phil));
    }

    public function testFindClassmatesOf()
    {
        $john = new DBStudent();
        $john->setName('john');
        $john->save();

        $jean = new DBStudent();
        $jean->setName('jean');
        $jean->save();

        $phil = new DBStudent();
        $phil->setName('phil');
        $phil->save();

        $this->assertEquals(0, DBStudentQuery::create()->findClassmatesOf($phil)->count());
        $this->assertEquals(0, DBStudentQuery::create()->findClassmatesOf($jean)->count());
        $this->assertEquals(0, DBStudentQuery::create()->findClassmatesOf($john)->count());

        $jean->addClassmate($phil);
        $this->assertEquals(0, DBStudentQuery::create()->findClassmatesOf($phil)->count());
        $this->assertEquals(0, DBStudentQuery::create()->findClassmatesOf($jean)->count());

        $jean->save();
        $this->assertEquals(1, DBStudentQuery::create()->findClassmatesOf($phil)->count());
        $this->assertEquals(1, DBStudentQuery::create()->findClassmatesOf($jean)->count());

        $coll = DBStudentQuery::create()->findClassmatesOf($phil);
        $this->assertInstanceOf('PropelObjectCollection', $coll);
        $this->assertInstanceOf('DBStudent', $coll[0]);
        $this->assertEquals('jean', $coll[0]->getName());

        $coll = DBStudentQuery::create()->findClassmatesOf($jean);
        $this->assertInstanceOf('PropelObjectCollection', $coll);
        $this->assertInstanceOf('DBStudent', $coll[0]);
        $this->assertEquals('phil', $coll[0]->getName());

        $jean->removeClassmates();
        $jean->save();

        $this->assertEquals(0, DBClassmateQuery::create()->count());

        $this->assertEquals(0, DBStudentQuery::create()->findClassmatesOf($phil)->count());
        $this->assertEquals(0, DBStudentQuery::create()->findClassmatesOf($jean)->count());
    }
}
