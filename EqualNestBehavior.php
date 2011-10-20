<?php

require_once dirname(__FILE__) . '/EqualNestParentBehavior.php';

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
class EqualNestBehavior extends Behavior
{
  // default parameters value
  protected $parameters = array(
    'parent_table'          => null,
    'reference_column_1'    => null,
    'reference_column_2'    => null,
  );

  /** @var EqualNestParentBehavior */
  protected $parentBehavior = null;

  public function modifyTable()
  {
    $table = $this->getTable();
    $parentTable = $this->getParentTable();
    if (count($parentTable->getPrimaryKey()) > 1)
    {
      throw new Exception('Equal nest works only with a single primary key for the parent table');
    }
    $parentTablePrimaryKey = $parentTable->getPrimaryKey();

    if (!$this->getTable()->containsColumn($this->getRefecenceColumn1Name()))
    {
      $this->getTable()->addColumn(array(
        'name' => $this->getRefecenceColumn1Name(),
        'primaryKey' => 'true',
        'type' => 'INTEGER'
      ));

      $fk = new ForeignKey();
      $fk->setName($this->getRefecenceColumn1Name());
      $fk->setForeignTableCommonName($this->getParentTable()->getCommonName());
      $fk->setOnDelete(ForeignKey::CASCADE);
      $fk->setOnUpdate(null);
      $fk->addReference($this->getRefecenceColumn1Name(), $parentTablePrimaryKey[0]->getName());
      $this->getTable()->addForeignKey($fk);
    }


    if (!$this->getTable()->containsColumn($this->getRefecenceColumn2Name()))
    {
      $this->getTable()->addColumn(array(
        'name' => $this->getRefecenceColumn2Name(),
        'primaryKey' => 'true',
        'type' => 'INTEGER',
      ));
      $fk = new ForeignKey();
      $fk->setName($this->getRefecenceColumn2Name());
      $fk->setForeignTableCommonName($this->getParentTable()->getCommonName());
      $fk->setOnDelete(ForeignKey::CASCADE);
      $fk->setOnUpdate(null);
      $fk->addReference($this->getRefecenceColumn2Name(), $parentTablePrimaryKey[0]->getName());
      $this->getTable()->addForeignKey($fk);
    }


    if (!$parentTable->hasBehavior('equal_nest_parent'))
    {
      $parentBehavior = new EqualNestParentBehavior();
      $parentBehavior->setName('equal_nest_parent');
      $parentBehavior->addParameter(array('name' => 'middle_table', 'value' => $this->getTable()->getName()));
      $parentTable->addBehavior($parentBehavior);
    }

    $this->parentBehavior = $parentTable->getBehavior('equal_nest_parent');
  }

  public function staticMethods($builder)
  {
    $this->builder = $builder;
    $script = '';
    $this->addPeerBuildEqualNestRelation($script);
    $this->addPeerRemoveEqualNestRelation($script);
    $this->addPeerCheckForEqualNestRelation($script);

    return $script;
  }

  public function addPeerBuildEqualNestRelation(&$script)
  {
    $script .= "
/**
 * Builds a new equal nest relation between {$this->parentBehavior->getTable()->getPhpName()} objects
 *
 * @param      {$this->parentBehavior->getTable()->getPhpName()}|integer \$object1
 * @param      {$this->parentBehavior->getTable()->getPhpName()}|integer \$object2
 * @param      PropelPDO \$con
 * @return     void
 */
public static function buildEqualNest{$this->getTable()->getPhpName()}Relation(\$object1, \$object2, PropelPDO \$con = null)
{
  if (self::checkForExistingEqualNest{$this->getTable()->getPhpName()}Relation(\$object1, \$object2, \$con = null))
  {
    return;
  }

  \$a{$this->getTable()->getPhpName()} = new {$this->getTable()->getPhpName()}();
  \$a{$this->getTable()->getPhpName()}->set{$this->getTable()->getColumn($this->getRefecenceColumn1Name())->getPhpName()}(is_object(\$object1) ? \$object1->getPrimaryKey() : \$object1);
  \$a{$this->getTable()->getPhpName()}->set{$this->getTable()->getColumn($this->getRefecenceColumn2Name())->getPhpName()}(is_object(\$object2) ? \$object2->getPrimaryKey() : \$object2);
  \$a{$this->getTable()->getPhpName()}->save();
}
";
  }

  public function addPeerRemoveEqualNestRelation(&$script)
  {
    $script .= "
/**
 * Removes a new equal nest relation between {$this->parentBehavior->getTable()->getPhpName()} objects
 *
 * @param      {$this->parentBehavior->getTable()->getPhpName()}|integer \$object1
 * @param      {$this->parentBehavior->getTable()->getPhpName()}|integer \$object2
 * @param      PropelPDO \$con
 * @return     void
 */
public static function removeEqualNest{$this->getTable()->getPhpName()}Relation(\$object1, \$object2, PropelPDO \$con = null)
{
  if (!\$relation = self::checkForExistingEqualNest{$this->getTable()->getPhpName()}Relation(\$object1, \$object2, \$con = null))
  {
    throw new PropelException('[Equal Nest] Cannot remove a relation that does not exist.');
  }

  \$relation->delete();
}
";
  }

  public function addPeerCheckForEqualNestRelation(&$script)
  {
    $fullNameRefColumn1 = $this->table->getPhpName(). '.' .$this->getReferenceColumn1()->getPhpName();
    $fullNameRefColumn2 = $this->table->getPhpName(). '.' .$this->getReferenceColumn2()->getPhpName();

    $script .= "
/**
 * Checks whether an equal nest relation between {$this->parentBehavior->getTable()->getPhpName()} objects
 *
 * @param      {$this->parentBehavior->getTable()->getPhpName()}|integer \$object1
 * @param      {$this->parentBehavior->getTable()->getPhpName()}|integer \$object2
 * @param      PropelPDO \$con
 * @return     {$this->getTable()->getPhpName()}|false
 */
public static function checkForExistingEqualNest{$this->getTable()->getPhpName()}Relation(\$object1, \$object2, PropelPDO \$con = null)
{
  if (\$object1 instanceof {$this->parentBehavior->getTable()->getPhpName()} && \$object1->isNew()) return false;
  if (\$object2 instanceof {$this->parentBehavior->getTable()->getPhpName()} && \$object2->isNew()) return false;

  return (\$relation = {$this->builder->getStubQueryBuilder()->getClassname()}::create()
    ->filterBy{$this->builder->getPluralizer()->getPluralForm($this->parentBehavior->getTable()->getPhpName())}(\$object1, \$object2)
    ->findOne(\$con)) ? \$relation : false;
}
";
  }


  public function queryMethods($builder)
  {
    $this->builder = $builder;
    $fullNameRefColumn1 = $this->table->getPhpName(). '.' .$this->getReferenceColumn1()->getPhpName();
    $fullNameRefColumn2 = $this->table->getPhpName(). '.' .$this->getReferenceColumn2()->getPhpName();

    $script = '';

    // filter by relation
    $script .= "
/**
 * Filter the query by 2 {$this->parentBehavior->getTable()->getPhpName()} objects for a Equal Nest {$this->getTable()->getPhpName()} relation
 *
 * @param      {$this->parentBehavior->getTable()->getPhpName()}|integer \$object1
 * @param      {$this->parentBehavior->getTable()->getPhpName()}|integer \$object2
 * @return     {$this->builder->getStubQueryBuilder()->getClassname()}
 */
public function filterBy{$this->builder->getPluralizer()->getPluralForm($this->parentBehavior->getTable()->getPhpName())}(\$object1, \$object2)
{
  return \$this
    ->condition('first-one', '$fullNameRefColumn1 = ?', is_object(\$object1) ? \$object1->getPrimaryKey() : \$object1)
    ->condition('first-two', '$fullNameRefColumn2 = ?', is_object(\$object2) ? \$object2->getPrimaryKey() : \$object2)
    ->condition('second-one', '$fullNameRefColumn2 = ?', is_object(\$object1) ? \$object1->getPrimaryKey() : \$object1)
    ->condition('second-two', '$fullNameRefColumn1 = ?', is_object(\$object2) ? \$object2->getPrimaryKey() : \$object2)
    ->combine(array('first-one',  'first-two'),  'AND', 'first')
    ->combine(array('second-one', 'second-two'), 'AND', 'second')
    ->where(array('first', 'second'), 'OR');
}
";

    return $script;
  }

  protected function getParentTable()
  {
    if (null === $this->getParameter('parent_table'))
    {
      throw new Exception('You must set a parent table for the Equal Nest behavior');
    }

    return $this->getTable()->getDatabase()->getTable($this->getParameter('parent_table'));
  }


  public function getRefecenceColumn1Name()
  {
    if (null === $this->getParameter('reference_column_1'))
    {
      return strtolower($this->getParentTable()->getPhpName()) . '_1';
    }
    else
    {
      return $this->getParameter('reference_column_1');
    }
  }

  public function getRefecenceColumn2Name()
  {
    if (null === $this->getParameter('reference_column_2'))
    {
      return strtolower($this->getParentTable()->getPhpName()) . '_2';
    }
    else
    {
      return $this->getParameter('reference_column_2');
    }
  }

  public function getReferenceColumn1()
  {
    return $this->table->getColumn($this->getRefecenceColumn1Name());
  }

  public function getReferenceColumn2()
  {
    return $this->table->getColumn($this->getRefecenceColumn2Name());
  }


}

