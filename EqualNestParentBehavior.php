<?php

require_once dirname(__FILE__) . '/EqualNestParentBehaviorObjectBuilderModifier.php';
require_once dirname(__FILE__) . '/EqualNestParentBehaviorQueryBuilderModifier.php';
require_once dirname(__FILE__) . '/EqualNestParentBehaviorPeerBuilderModifier.php';

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
 *
 * @author      Ivan Plamenov Tanev aka Crafty_Shadow @ WEBWORLD.BG <vankata.t@gmail.com>
 * @package     propel.generator.behavior.equal_nest
 */
class EqualNestParentBehavior extends Behavior
{

  protected $objectBuilderModifier, $queryBuilderModifier, $peerBuilderModifier;

  protected $parameters = array(
    'middle_table' => null,
  );

  public function getMiddleTable()
  {
    return $this->getTable()->getDatabase()->getTable($this->getParameter('middle_table'));
  }

  public function getObjectBuilderModifier()
  {
    if (is_null($this->objectBuilderModifier))
    {
      $this->objectBuilderModifier = new EqualNestParentBehaviorObjectBuilderModifier($this, $this->getMiddleTable());
    }
    return $this->objectBuilderModifier;
  }

  public function getQueryBuilderModifier()
  {
    if (is_null($this->queryBuilderModifier))
    {
      $this->queryBuilderModifier = new EqualNestParentBehaviorQueryBuilderModifier($this, $this->getMiddleTable());
    }
    return $this->queryBuilderModifier;
  }

  public function getPeerBuilderModifier()
  {
    if (is_null($this->peerBuilderModifier))
    {
      $this->peerBuilderModifier = new EqualNestParentBehaviorPeerBuilderModifier($this, $this->getMiddleTable());
    }
    return $this->peerBuilderModifier;
  }

}