<?php

/**
 * This file is part of the Equal Nest Behavior package.
 * For the full copyright and license information, please view the README.md
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * @author      Ivan Plamenov Tanev aka Crafty_Shadow @ WEBWORLD.BG <vankata.t@gmail.com>
 */
class EqualNestParentBehaviorQueryBuilderModifier
{
    protected $behavior;

    protected $table;

    protected $middleTable;

    public function __construct($behavior, $middleTable)
    {
        $this->behavior     = $behavior;
        $this->table        = $behavior->getTable();
        $this->middleTable = $middleTable;
    }

    public function queryMethods($builder)
    {
        $script = '';

        $script .= $this->addFindRelatedObjects($builder);
        $script .= $this->addCountRelatedObjects($builder);

        return $script;
    }

    protected function addFindRelatedObjects($builder)
    {
        return $this->behavior->renderTemplate('addFindRelatedObjects', array(
            'pluralRefTableName'  => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'objectClassname'     => $builder->getStubObjectBuilder()->getClassname(),
            'objectName'          => '$' . $this->table->getStudlyPhpName(),
        ), '/templates/parent/');
    }

    protected function addCountRelatedObjects($builder)
    {
        return $this->behavior->renderTemplate('addCountRelatedObjects', array(
            'pluralRefTableName'  => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'objectClassname'     => $builder->getStubObjectBuilder()->getClassname(),
            'objectName'          => '$' . $this->table->getStudlyPhpName(),
        ), '/templates/parent/');
    }
}
