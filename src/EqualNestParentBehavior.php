<?php

/**
 * This file is part of the Equal Nest Behavior package.
 * For the full copyright and license information, please view the README.md
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Gives a model class the ability to support Equal Nest relations
 *
 * @author      Ivan Plamenov Tanev aka Crafty_Shadow @ WEBWORLD.BG <vankata.t@gmail.com>
 */
class EqualNestParentBehavior extends Behavior
{
    protected $objectBuilderModifier;

    protected $queryBuilderModifier;

    protected $parameters = array(
        'middle_table' => null,
    );

    public function getMiddleTable()
    {
        return $this->getTable()->getDatabase()->getTable($this->getParameter('middle_table'));
    }

    public function getObjectBuilderModifier()
    {
        if (null === $this->objectBuilderModifier) {
            $this->objectBuilderModifier = new EqualNestParentBehaviorObjectBuilderModifier($this, $this->getMiddleTable());
        }

        return $this->objectBuilderModifier;
    }

    public function getQueryBuilderModifier()
    {
        if (null === $this->queryBuilderModifier) {
            $this->queryBuilderModifier = new EqualNestParentBehaviorQueryBuilderModifier($this, $this->getMiddleTable());
        }

        return $this->queryBuilderModifier;
    }
}
