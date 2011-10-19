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
class EqualNestParentBehaviorQueryBuilderModifier
{
  protected $behavior, $table, $builder, $objectClassname, $peerClassname;

  /** @var Table */
  protected $middle_table;

  public function __construct($behavior, $middle_table)
  {
    $this->behavior = $behavior;
    $this->table = $behavior->getTable();
    $this->middle_table = $middle_table;
  }

  protected function getParameter($key)
  {
    return $this->behavior->getParameter($key);
  }

  protected function getColumn($name)
  {
    return $this->behavior->getColumnForParameter($name);
  }

  protected function setBuilder($builder)
  {
    $this->builder = $builder;
    $this->objectClassname = $builder->getStubObjectBuilder()->getClassname();
    $this->queryClassname = $builder->getStubQueryBuilder()->getClassname();
    $this->peerClassname = $builder->getStubPeerBuilder()->getClassname();
  }

  public function queryMethods($builder)
  {
    $this->setBuilder($builder);
    $script = '';

    $this->addFindRelatedObjects($script);
    $this->addCountRelatedObjects($script);



    return $script;
  }

  public function addFindRelatedObjects(&$script)
  {
    $objectName = '$' . $this->table->getStudlyPhpName();

    $script .= "
/**
 * Find equal nest {$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())} of the supplied {$this->objectClassname} object
 *
 * @param      {$this->objectClassname} $objectName
 * @param      PropelPDO \$con
 * @return     {$this->objectClassname}[]|PropelObjectCollection
 */
public function find{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}Of({$this->objectClassname} $objectName, \$con = null)
{
  \$obj = clone $objectName;
  \$obj->clearList{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs();
  \$obj->clear{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}();

  return \$obj->get{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}(\$this, \$con);
}
";
  }

  public function addCountRelatedObjects(&$script)
  {
    $objectName = '$' . $this->table->getStudlyPhpName();

    $script .= "
/**
 * Count equal nest {$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())} of the supplied {$this->objectClassname} object
 *
 * @param      {$this->objectClassname} $objectName
 * @param      PropelPDO \$con
 * @return     integer
 */
public function count{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}Of({$this->objectClassname} $objectName, \$con = null)
{
  \$obj = clone $objectName;
  \$obj->clearList{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs();
  \$obj->clear{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}();

  return \$obj->count{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}(\$this, \$con);
}
";
  }


}