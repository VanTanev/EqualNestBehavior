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
        }

        $this->getBuilder($schema)->build();
    }

    public function testObjectMethods()
    {
    }
}
