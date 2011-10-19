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
 * @package     propel.generator.behavior.equal_nest
 */
class EqualNestParentBehaviorPeerBuilderModifier
{
  protected $behavior,  $builder, $objectClassname, $peerClassname;

  /** @var Table */
  protected $table;

  /** @var Table */
  protected $middle_table;

  /** @var EqualNestBehavior */
  protected $middle_behavior;

  public function __construct($behavior, $middle_table)
  {
    $this->behavior = $behavior;
    $this->table = $behavior->getTable();
    $this->middle_table = $middle_table;
    $this->middle_behavior = $this->middle_table->getBehavior('equal_nest');
  }

  protected function getParameter($key)
  {
    return $this->behavior->getParameter($key);
  }

  protected function getColumn($name)
  {
    return $this->behavior->getColumnForParameter($name);
  }

  protected function getColumnAttribute($name)
  {
    return strtolower($this->getColumn($name)->getName());
  }

  protected function getColumnConstant($name)
  {
    return strtoupper($this->getColumn($name)->getName());
  }

  protected function getColumnPhpName($name)
  {
    return $this->getColumn($name)->getPhpName();
  }

  protected function setBuilder($builder)
  {
    $this->builder = $builder;
    $this->objectClassname = $builder->getStubObjectBuilder()->getClassname();
    $this->peerClassname = $builder->getStubPeerBuilder()->getClassname();
  }

  public function staticAttributes($builder)
  {
    $this->builder = $builder;
    $tableName = $this->table->getName();
    $pks = $this->table->getPrimaryKey();
    $tablePk = $pks[0]->getFullyQualifiedName();

    $middleTableName = $this->middle_table->getName();
    $ucMiddleTableName = strtoupper($this->middle_table->getPhpName());
    $ref_column_1 = $this->middle_behavior->getReferenceColumn1();
    $ref_column_2 = $this->middle_behavior->getReferenceColumn2();

    $script = "
/**
 *  Select query to get the IDs that will be used for IN clauses to retrieve the {$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}
 */
const LIST_EQUAL_NEST_{$ucMiddleTableName}_PKs_QUERY = '
  SELECT DISTINCT $tablePk FROM $tableName
  INNER JOIN $middleTableName ON
    $tablePk = {$ref_column_1->getFullyQualifiedName()}
    OR
    $tablePk = {$ref_column_2->getFullyQualifiedName()}
  WHERE
    $tablePk IN (
      SELECT {$ref_column_1->getFullyQualifiedName()} FROM $middleTableName WHERE {$ref_column_2->getFullyQualifiedName()} = :{$pks[0]->getStudlyPhpName()}
    )
    OR
    $tablePk IN (
      SELECT {$ref_column_2->getFullyQualifiedName()} FROM $middleTableName WHERE {$ref_column_1->getFullyQualifiedName()} = :{$pks[0]->getStudlyPhpName()}
    )
';

";

    return $script;
  }
}