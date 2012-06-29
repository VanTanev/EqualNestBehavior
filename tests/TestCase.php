<?php

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function getBuilder($schema)
    {
        $builder = new PropelQuickBuilder();
        $config  = $builder->getConfig();
        $config->setBuildProperty('behavior.equal_nest.class', '../src/EqualNestBehavior');

        $builder->setConfig($config);
        $builder->setSchema($schema);

        return $builder;
    }
}
