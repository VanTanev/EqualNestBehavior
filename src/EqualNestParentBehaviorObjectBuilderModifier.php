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
class EqualNestParentBehaviorObjectBuilderModifier
{
    protected $behavior;

    protected $table;

    protected $builder;

    protected $middleTable;

    protected $middleBehavior;

    public function __construct($behavior, $middleTable)
    {
        $this->behavior         = $behavior;
        $this->table            = $behavior->getTable();
        $this->middleTable      = $middleTable;
        $this->middleBehavior   = $this->middleTable->getBehavior('equal_nest');
    }

    public function objectAttributes($builder)
    {
        $objectClassname = $builder->getStubObjectBuilder()->getClassname();

        return $this->behavior->renderTemplate('objectAttributes', array(
            'objectClassname' => $objectClassname,
            'refClassName'    => $this->middleTable->getPhpName(),
            'collName'        => $this->getEqualNestCollectionName($builder),
            'listName'        => $this->getEqualNestListPksName($builder),
        ), '/templates/parent/');
    }

    public function objectMethods($builder)
    {
        $builder->declareClassFromBuilder($builder->getNewStubQueryBuilder($this->middleTable));
        $builder->declareClassFromBuilder($builder->getNewStubPeerBuilder($this->middleTable));

        $script = '';

        $script .= $this->addPorcessEqualNestQueries($builder);
        $script .= $this->addClearListRelatedPKs($builder);

        $script .= $this->addInitListRelatedPKs($builder);
        $script .= $this->addClearRelatedCollection($builder);

        $script .= $this->addInitRelatedCollection($builder);
        $script .= $this->addRemoveAllRelations($builder);

        $script .= $this->addGetRelatedCollection($builder);
        $script .= $this->addSetRelatedColelction($builder);

        $script .= $this->hasObjectInRelatedCollection($builder);
        $script .= $this->setObjectsOfRelatedCollection($builder);

        $script .= $this->addObjectToRelatedCollection($builder);
        $script .= $this->removeObjectFromRelatedCollection($builder);

        $script .= $this->countObjectsInRelatedCollection($builder);

        return $script;
    }

    public function postSave($builder)
    {
        return $this->behavior->renderTemplate('postSave', array(), '/templates/parent/');
    }

    public function objectClearReferences($builder)
    {
        return $this->behavior->renderTemplate('objectClearReferences', array(
            'collName' => $this->getEqualNestCollectionName($builder),
            'listName' => $this->getEqualNestListPksName($builder),
        ), '/templates/parent/');
    }

    public function addPorcessEqualNestQueries($builder)
    {
        return $this->behavior->renderTemplate('processEqualNestQueries', array(
            'collName'            => $this->getEqualNestCollectionName($builder),
            'listName'            => $this->getEqualNestListPksName($builder),
            'peerClassname'       => $builder->getStubPeerBuilder()->getClassname(),
            'refPeerClassname'    => $builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname(),
            'refTableName'        => $this->middleTable->getPhpName(),
            'pluralRefTableName'  => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
        ), '/templates/parent/');
    }

    public function addClearListRelatedPKs($builder)
    {
        return $this->behavior->renderTemplate('clearListRelatedPks', array(
            'pluralRefTableName' => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'listName'           => $this->getEqualNestListPksName($builder),
        ), '/templates/parent/');
    }

    public function addInitListRelatedPKs($builder)
    {
        $pk = current($this->table->getPrimaryKey());

        return $this->behavior->renderTemplate('addInitListRelatedPKs', array(
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'objectClassname'       => $builder->getStubObjectBuilder()->getClassname(),
            'peerClassname'         => $builder->getStubPeerBuilder()->getClassname(),
            'varListRelatedPKs'     => $this->getEqualNestListPksName($builder),
            'pkName'                => $pk->getStudlyPhpName(),
            'tablePk'               => $pk->getFullyQualifiedName(),
            'tableName'             => $this->table->getName(),
            'middleTableName'       => $this->middleTable->getName(),
            'refColumn1'            => $this->middleBehavior->getReferenceColumn1()->getFullyQualifiedName(),
            'refColumn2'            => $this->middleBehavior->getReferenceColumn2()->getFullyQualifiedName(),
        ), '/templates/parent/');
    }

    public function addClearRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('addClearRelatedCollection', array(
            'refTableName'          => $this->middleTable->getPhpName(),
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'varRelatedObjectsColl' => $this->getEqualNestCollectionName($builder),
        ), '/templates/parent/');
    }

    public function addInitRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('addInitRelatedCollection', array(
            'varRelatedObjectsColl' => $this->getEqualNestCollectionName($builder),
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'objectClass'           => $builder->getStubObjectBuilder()->getFullyQualifiedClassname(),
        ), '/templates/parent/');
    }

    public function addRemoveAllRelations($builder)
    {
        return $this->behavior->renderTemplate('addRemoveAllRelations', array(
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'refTableName'          => $this->middleTable->getPhpName(),
        ), '/templates/parent/');
    }

  public function addGetRelatedCollection($builder)
  {
    $refTableName = $this->middleTable->getPhpName();
    $pluralRefTableName = $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName());
    $peerClassname = $builder->getStubPeerBuilder()->getClassname();
    $objectClassname = $builder->getStubObjectBuilder()->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}";

    return "
/**
 * Gets an array of {$objectClassname} objects which are Equal Nest $pluralRefTableName of this object.
 *
 * If the \$criteria is not null, it is used to always fetch the results from the database.
 * Otherwise the results are fetched from the database the first time, then cached.
 * Next time the same method is called without \$criteria, the cached collection is returned.
 * If this {$objectClassname} object is new, it will return an empty collection; the criteria is ignored on a new object.
 *
 * @param      Criteria \$criteria
 * @param      PropelPDO \$con
 * @return     PropelObjectCollection {$objectClassname}[] List of Equal Nest $pluralRefTableName of this {$objectClassname}
 * @throws     PropelException
 */
public function get$pluralRefTableName(Criteria \$criteria = null, PropelPDO \$con = null)
{
  if (null === \$this->$varListRelatedPKs) {
    \$this->initList{$pluralRefTableName}PKs(\$con);
  }

  if (null === \$this->$varRelatedObjectsColl || null !== \$criteria) {
    if (array() === \$this->$varListRelatedPKs && null === \$this->$varRelatedObjectsColl) {
      // return empty collection
      \$this->init$pluralRefTableName();
    } else {
      \$new_collection = {$builder->getStubQueryBuilder()->getClassname()}::create(null, \$criteria)
        ->addUsingAlias({$pk->getConstantName()}, \$this->$varListRelatedPKs, Criteria::IN)
        ->find(\$con);
      if (null !== \$criteria) {
        return \$new_collection;
      }
      \$this->$varRelatedObjectsColl = \$new_collection;
    }
  }

  return \$this->$varRelatedObjectsColl;
}
";
  }

  public function addSetRelatedColelction($builder)
  {
    $refTableName = $this->middleTable->getPhpName();
    $pluralRefTableName = $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName());
    $peerClassname = $builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();
    $objectClassname = $builder->getStubObjectBuilder()->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}";

    return "
/**
 * Set an array of {$objectClassname} objects as $pluralRefTableName of the this object
 *
 * @param      {$objectClassname}[] \$objects The {$objectClassname} objects to set as $pluralRefTableName of the current object
 * @return     void
 * @throws     PropelException
 * @see        add$refTableName()
 */
public function set$pluralRefTableName(\$objects)
{
  \$this->clear$pluralRefTableName();
  foreach (\$objects as \$a$refTableName) {
    if (!\$a$refTableName instanceof {$objectClassname}) {
      throw new PropelException(sprintf('[Equal Nest] Cannot set object of type %s as $refTableName, expected {$objectClassname}', is_object(\$a$refTableName) ? get_class(\$a$refTableName) : gettype(\$a$refTableName)));
    } else {
      \$this->add$refTableName(\$a$refTableName);
    }
  }
}
";
  }

  public function hasObjectInRelatedCollection($builder)
  {
    $refTableName = $this->middleTable->getPhpName();
    $pluralRefTableName = $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName());
    $peerClassname = $builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();
    $objectClassname = $builder->getStubObjectBuilder()->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}";

    return "
/**
 * Checks for Equal Nest relation
 *
 * @param      {$objectClassname} \$a$refTableName The object to check for Equal Nest $refTableName relation to the current object
 * @return     boolean
 */
public function has$refTableName({$objectClassname} \$a$refTableName)
{
  if (null === \$this->$varRelatedObjectsColl) {
    \$this->get$pluralRefTableName();
  }

  return \$a{$refTableName}->isNew() || \$this->isNew()
    ? in_array(\$a$refTableName, \$this->{$varRelatedObjectsColl}->getArrayCopy())
    : in_array(\$a{$refTableName}->getPrimaryKey(), \$this->{$varRelatedObjectsColl}->getPrimaryKeys());
}
";
  }

  public function setObjectsOfRelatedCollection($builder)
  {
    $refTableName = $this->middleTable->getPhpName();
    $pluralRefTableName = $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName());
    $peerClassname = $builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();
    $objectClassname = $builder->getStubObjectBuilder()->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}";

    return "
/**
 * Method called to associate another {$objectClassname} object as a $refTableName of this one
 * through the Equal Nest $pluralRefTableName relation.
 *
 * @param      {$objectClassname} \$a$refTableName The {$objectClassname} object to set as Equal Nest $pluralRefTableName relation of the current object
 * @return     void
 * @throws     PropelException
 */
public function add$refTableName({$objectClassname} \$a$refTableName)
{
  if (!\$this->has$refTableName(\$a$refTableName)) {
    \$this->{$varRelatedObjectsColl}[] = \$a$refTableName;
    \$a{$refTableName}->add{$refTableName}(\$this);
  }
}
";
  }

    public function addObjectToRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('addObjectToRelatedCollection', array(
            'objectClassname'       => $builder->getStubObjectBuilder()->getClassname(),
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'refTableName'          => $this->middleTable->getPhpName(),
        ), '/templates/parent/');
    }

    public function removeObjectFromRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('removeObjectFromRelatedCollection', array(
            'refTableName'         => $this->middleTable->getPhpName(),
            'varRefTableName'      => '$' . lcfirst($this->middleTable->getPhpName()),
            'pluralRefTableName'   => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'objectClassname'      => $builder->getStubObjectBuilder()->getClassname(),
            'varRelObjectsColl'    => $this->getEqualNestCollectionName($builder),
        ), '/templates/parent/');
    }

  public function countObjectsInRelatedCollection($builder)
  {
    $refTableName = $this->middleTable->getPhpName();
    $pluralRefTableName = $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName());
    $peerClassname = $builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}";

    return "
/**
 * Returns the number of Equal Nest $pluralRefTableName of this object.
 *
 * @param      Criteria \$criteria
 * @param      boolean \$distinct
 * @param      PropelPDO \$con
 * @return     integer Count of \$pluralRefTableName
 * @throws     PropelException
 */
public function count$pluralRefTableName(Criteria \$criteria = null, \$distinct = false, PropelPDO \$con = null)
{
  if (null === \$this->$varListRelatedPKs) {
    \$this->initList{$pluralRefTableName}PKs(\$con);
  }

  if (null === \$this->$varRelatedObjectsColl || null !== \$criteria) {
    if (\$this->isNew() && null === \$this->$varRelatedObjectsColl) {
      return 0;
    } else {
      \$query = {$builder->getStubQueryBuilder()->getClassname()}::create(null, \$criteria);
      if (\$distinct) {
        \$query->distinct();
      }

      return \$query
        ->addUsingAlias({$pk->getConstantName()}, \$this->$varListRelatedPKs, Criteria::IN)
        ->count(\$con);
    }
  } else {
    return count(\$this->$varRelatedObjectsColl);
  }
}
";
  }

    protected function getMiddleTable()
    {
        return $this->getTable()->getDatabase()->getTable($this->getParameter('extends'));
    }

    protected function getParameter($key)
    {
        return $this->behavior->getParameter($key);
    }

    protected function getColumnAttribute($name)
    {
        return strtolower($this->behavior->getColumnForParameter($name)->getName());
    }

    protected function getColumnPhpName($name)
    {
        return $this->behavior->getColumnForParameter($name)->getPhpName();
    }

    protected function getEqualNestCollectionName($builder)
    {
        return 'collEqualNest' .  $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName());
    }

    protected function getEqualNestListPksName($builder)
    {
        return sprintf('listEqualNest%sPKs', $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()));
    }
}
