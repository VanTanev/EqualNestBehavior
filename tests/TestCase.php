<?php

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    public function assertSQLContains($tablename, $options, $expected)
    {
        $schema  = $this->generateSchema($tablename, $options);
        $builder = $this->getBuilder($schema);

        $this->assertContains($expected, $builder->getSQL());
    }

    protected function getBuilder($schema)
    {
        $builder = new PropelQuickBuilder();
        $config  = $builder->getConfig();
        $config->setBuildProperty('behavior.publishable.class', __DIR__ . '/../src/EqualNestBehavior');

        $builder->setConfig($config);
        $builder->setSchema($schema);

        return $builder;
    }
}
