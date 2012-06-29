<?php

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class EqualNestBehaviorTest extends TestCase
{
    protected function setUp()
    {
        if (!class_exists('')) {
            $schema = <<<XML
<database name="equal_nest_behavior">
</database>
XML;
        }

        $this->getBuilder($schema)->build();
    }
}
