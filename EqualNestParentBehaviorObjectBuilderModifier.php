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
class EqualNestParentBehaviorObjectBuilderModifier
{
  protected $behavior, $table, $builder, $objectClassname, $peerClassname;

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

  protected function setBuilder($builder)
  {
    $this->builder = $builder;
    $this->objectClassname = $builder->getStubObjectBuilder()->getClassname();
    $this->queryClassname = $builder->getStubQueryBuilder()->getClassname();
    $this->peerClassname = $builder->getStubPeerBuilder()->getClassname();
  }


  public function objectAttributes($builder)
  {
    $this->builder = $builder;
    $objectClassname = $builder->getStubObjectBuilder()->getClassname();

    return "
/**
 * @var        array List of PKs of {$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())} for this $objectClassname
 */
protected \$listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs;


/**
 * @var        PropelObjectCollection09 {$objectClassname}[] Collection to store Equal Nest {$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())} of this $objectClassname
 */
protected \$collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())};

/**
 * @var        boolean Flag to prevent endless processing loop which occurs when 2 new objects are set as twins
 */
protected \$alreadyInEqualNestProcessing = false;
";
  }

  public function postSave($builder)
  {
    return "\$this->processEqualNestQueries(\$con);";
  }


  public function objectClearReferences($builder)
  {
    $this->builder = $builder;

    return "
if (\$deep) {
  if (\$this->collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}) {
    foreach ((array) \$this->collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())} as \$o) {
      \$o->clearAllReferences(\$deep);
    }
  }
}

\$this->listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs = null;
\$this->collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())} = null;
";
  }


  public function objectMethods($builder)
  {
    $this->setBuilder($builder);
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $script = '';


    $this->addPorcessEqualNestQueries($script);
    $this->addClearListRelatedPKs($script);
    $this->addInitListRelatedPKs($script);
    $this->addClearRelatedCollection($script);
    $this->addInitRelatedCollection($script);
    $this->addRemoveAllRelations($script);
    $this->addGetRelatedCollection($script);
    $this->addSetRelatedColelction($script);
    $this->hasObjectInRelatedCollection($script);
    $this->setObjectsOfRelatedCollection($script);
    $this->addObjectToRelatedCollection($script);
    $this->removeObjectFromRelatedCollection($script);
    $this->countObjectsInRelatedCollection($script);

    return $script;
  }


  public function addPorcessEqualNestQueries(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $this->builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();

    $script .= "
/**
 * This function checks the local equal nest collection against the database
 * and creates new relations or deletes ones that have been removed
 *
 * @param      PropelPDO \$con
 */
public function processEqualNestQueries(PropelPDO \$con = null)
{
  if (false == \$this->alreadyInEqualNestProcessing && null !== \$this->collEqualNest$pluralRefTableName) {

    if (\$con === null) {
      \$con = Propel::getConnection({$peerClassname}::DATABASE_NAME, Propel::CONNECTION_WRITE);
    }

    \$this->alreadyInEqualNestProcessing = true;

    \$this->clearList{$pluralRefTableName}PKs();
    \$this->initList{$pluralRefTableName}PKs(\$con);

    \$this->collEqualNest{$pluralRefTableName}->save();

    \$con->beginTransaction();
    try {

      foreach (\$this->get$pluralRefTableName()->getPrimaryKeys(\$usePrefix = false) as \$col_key => \$pk)
      {
        if (!in_array(\$pk, \$this->listEqualNest{$pluralRefTableName}PKs)) {
          // save new equal nest relation
          $refPeerClassname::buildEqualNest{$refTableName}Relation(\$this, \$pk, \$con);
          // add this object to the sibling's collection
          \$this->get$pluralRefTableName()->get(\$col_key)->add{$refTableName}(\$this);
        } else {
          // remove the pk from the list of db keys
          unset(\$this->listEqualNest{$pluralRefTableName}PKs[array_search(\$pk, \$this->listEqualNest{$pluralRefTableName}PKs)]);
        }
      }

      // if we have keys still left, this means they are relations that have to be removed
      foreach (\$this->listEqualNest{$pluralRefTableName}PKs as \$old_pk)
      {
        $refPeerClassname::removeEqualNest{$refTableName}Relation(\$this, \$old_pk, \$con);
      }

      \$con->commit();
      \$this->alreadyInEqualNestProcessing = false;
    } catch (PropelException \$e) {
      \$con->rollBack();
      throw \$e;
    }
  }
}
";
  }


  public function addClearListRelatedPKs(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";


    $script .= "
/**
 * Clears out the list of Equal Nest $pluralRefTableName PKs
 *
 * @return     void
 */
public function clearList{$pluralRefTableName}PKs()
{
  \$this->$varListRelatedPKs = null;
}
";
  }


  public function addInitListRelatedPKs(&$script)
  {
    $ucMiddleTableName = strtoupper($this->middle_table->getPhpName());
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";


    $script .= "
/**
 * Initializes the list of Equal Nest $pluralRefTableName PKs.
 *
 * This will query the database for Equal Nest $pluralRefTableName relations to this {$this->objectClassname} object.
 * It will set the list to an empty array if the object is newly created.
 *
 * @param      PropelPDO \$con
 * @return     void
 */
protected function initList{$pluralRefTableName}PKs(PropelPDO \$con = null)
{
  if (\$con === null) {
    \$con = Propel::getConnection({$peerClassname}::DATABASE_NAME, Propel::CONNECTION_READ);
  }

  if (null === \$this->$varListRelatedPKs) {
    if (\$this->isNew()) {
      \$this->$varListRelatedPKs = array();
    } else {
      \$stmt = \$con->prepare($peerClassname::LIST_EQUAL_NEST_{$ucMiddleTableName}_PKs_QUERY);
      \$stmt->bindValue(':{$pk->getStudlyPhpName()}', \$this->getPrimaryKey(), PDO::PARAM_INT);
      \$stmt->execute();

      \$this->$varListRelatedPKs = \$stmt->fetchAll(PDO::FETCH_COLUMN);
    }
  }
}
";
  }


  public function addClearRelatedCollection(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());

    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";


    $script .= "
/**
 * Clears out the collection of Equal Nest $pluralRefTableName
 *
 * This does not modify the database; however, it will remove any associated objects, causing
 * them to be refetched by subsequent calls to the accessor method.
 *
 * @return     void
 * @see        add$refTableName()
 * @see        set$pluralRefTableName()
 * @see        remove$pluralRefTableName()
 */
public function clear$pluralRefTableName()
{
  \$this->$varRelatedObjectsColl = null;
}

";
  }


  public function addInitRelatedCollection(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";


    $script .= "
/**
 * Initializes the $varRelatedObjectsColl collection.
 *
 * By default this just sets the $varRelatedObjectsColl collection to an empty PropelObjectCollection;
 * however, you may wish to override this method in your stub class to provide setting appropriate
 * to your application -- for example, setting the initial array to the values stored in database (ie, calling get$pluralRefTableName).
 *
 * @return     void
 */
protected function init$pluralRefTableName()
{
  \$this->$varRelatedObjectsColl = new PropelObjectCollection();
  \$this->{$varRelatedObjectsColl}->setModel('{$this->objectClassname}');
}
";
  }


  public function addRemoveAllRelations(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());

    $script .= "
/**
 * Removes all Equal Nest $pluralRefTableName relations
 *
 * @return     void
 * @see        add$refTableName()
 * @see        set$pluralRefTableName()
 */
public function remove$pluralRefTableName()
{
  // this sets the collection to an empty Propel object collection; upon save, all relations will be removed
  self::init$pluralRefTableName();
}
";
  }


  public function addGetRelatedCollection(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";

    $script .= "
/**
 * Gets an array of {$this->objectClassname} objects which are Equal Nest $pluralRefTableName of this object.
 *
 * If the \$criteria is not null, it is used to always fetch the results from the database.
 * Otherwise the results are fetched from the database the first time, then cached.
 * Next time the same method is called without \$criteria, the cached collection is returned.
 * If this {$this->objectClassname} object is new, it will return an empty collection; the criteria is ignored on a new object.
 *
 * @param      Criteria \$criteria
 * @param      PropelPDO \$con
 * @return     PropelObjectCollection {$this->objectClassname}[] List of Equal Nest $pluralRefTableName of this {$this->objectClassname}
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
      \$new_collection = {$this->builder->getStubQueryBuilder()->getClassname()}::create(null, \$criteria)
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

  public function addSetRelatedColelction(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $this->builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";

    $script .= "
/**
 * Set an array of {$this->objectClassname} objects as $pluralRefTableName of the this object
 *
 * @param      {$this->objectClassname}[] \$objects The {$this->objectClassname} objects to set as $pluralRefTableName of the current object
 * @return     void
 * @throws     PropelException
 * @see        add$refTableName()
 */
public function set$pluralRefTableName(\$objects)
{
  \$this->clear$pluralRefTableName();
  foreach (\$objects as \$a$refTableName)
  {
    if (!\$a$refTableName instanceof {$this->objectClassname}) {
      throw new PropelException(sprintf('[Equal Nest] Cannot set object of type %s as $refTableName, expected {$this->objectClassname}', is_object(\$a$refTableName) ? get_class(\$a$refTableName) : gettype(\$a$refTableName)));
    } else {
      \$this->add$refTableName(\$a$refTableName);
    }
  }
}
";
  }


  public function hasObjectInRelatedCollection(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $this->builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";

    $script .= "
/**
 * Checks for Equal Nest relation
 *
 * @param      {$this->objectClassname} \$a$refTableName The object to check for Equal Nest $refTableName relation to the current object
 * @return     boolean
 */
public function has$refTableName({$this->objectClassname} \$a$refTableName)
{
  if (null === \$this->$varRelatedObjectsColl)
  {
    \$this->get$pluralRefTableName();
  }

  return \$a{$refTableName}->isNew() || \$this->isNew()
    ? in_array(\$a$refTableName, \$this->{$varRelatedObjectsColl}->getArrayCopy())
    : in_array(\$a{$refTableName}->getPrimaryKey(), \$this->{$varRelatedObjectsColl}->getPrimaryKeys());
}
";
  }


  public function setObjectsOfRelatedCollection(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $this->builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";

    $script .= "
/**
 * Method called to associate another {$this->objectClassname} object as a $refTableName of this one
 * through the Equal Nest $pluralRefTableName relation.
 *
 * @param      {$this->objectClassname} \$a$refTableName The {$this->objectClassname} object to set as Equal Nest $pluralRefTableName relation of the current object
 * @return     void
 * @throws     PropelException
 */
public function add$refTableName({$this->objectClassname} \$a$refTableName)
{
  if (!\$this->has$refTableName(\$a$refTableName)) {
    \$this->{$varRelatedObjectsColl}[] = \$a$refTableName;
    \$a{$refTableName}->add{$refTableName}(\$this);
  }
}
";
  }


  public function addObjectToRelatedCollection(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $this->builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";

    $script .= "
/**
 * Method called to associate multiple {$this->objectClassname} objects as Equal Nest $pluralRefTableName of this one
 *
 * @param      {$this->objectClassname}[] $pluralRefTableName The {$this->objectClassname} objects to set as Equal Nest $pluralRefTableName relation of the current object
 * @return     void
 * @throws     PropelException
 */
public function add$pluralRefTableName(\$$pluralRefTableName)
{
  foreach (\$$pluralRefTableName as \$a$pluralRefTableName)
  {
    \$this->add$refTableName(\$a$pluralRefTableName);
  }
}
";
  }


  public function removeObjectFromRelatedCollection(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $this->builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";

    $script .= "
/**
 * Method called to remove a {$this->objectClassname} object from the Equal Nest $pluralRefTableName relation
 *
 * @param      {$this->objectClassname} \$a$refTableName The {$this->objectClassname} object to remove as a $refTableName of the current object
 * @return     void
 * @throws     PropelException
 */
public function remove$refTableName({$this->objectClassname} \$a$refTableName)
{
  if (null === \$this->$varRelatedObjectsColl) {
    \$this->get$pluralRefTableName();
  }

  if (\$this->{$varRelatedObjectsColl}->contains(\$a$refTableName)) {
    \$this->{$varRelatedObjectsColl}->remove(\$this->{$varRelatedObjectsColl}->search(\$a$refTableName));
  } else {
    throw new PropelException(sprintf('[Equal Nest] Cannot remove $refTableName from Equal Nest relation because it is not set as one!'));
  }
}
";
  }


  public function countObjectsInRelatedCollection(&$script)
  {
    $refTableName = $this->middle_table->getPhpName();
    $pluralRefTableName = $this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName());
    $peerClassname = $this->builder->getStubPeerBuilder()->getClassname();
    $refPeerClassname = $this->builder->getNewStubPeerBuilder($this->behavior->getMiddleTable())->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}";

    $script .= "
/**
 * Returns the number of Equal Nest $pluralRefTableName of this object.
 *
 * @param      Criteria $criteria
 * @param      boolean $distinct
 * @param      PropelPDO $con
 * @return     integer Count of $pluralRefTableName
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
      \$query = {$this->builder->getStubQueryBuilder()->getClassname()}::create(null, \$criteria);
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


}