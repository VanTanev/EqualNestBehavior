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
    $ucMiddleTableName = strtoupper($this->middleTable->getPhpName());
    $pluralRefTableName = $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName());
    $peerClassname = $builder->getStubPeerBuilder()->getClassname();
    $objectClassname = $builder->getStubObjectBuilder()->getClassname();

    $pk = $this->table->getPrimaryKey(); /** @var Column */ $pk = $pk[0];

    $varListRelatedPKs = "listEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}PKs";
    $varRelatedObjectsColl = "collEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}";

    return "
/**
 * Initializes the list of Equal Nest $pluralRefTableName PKs.
 *
 * This will query the database for Equal Nest $pluralRefTableName relations to this {$objectClassname} object.
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

  public function addClearRelatedCollection($builder)
  {
    $refTableName = $this->middleTable->getPhpName();
    $pluralRefTableName = $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName());

    $varRelatedObjectsColl = "collEqualNest{$builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName())}";

    return "
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

  public function addInitRelatedCollection($builder)
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
  \$this->{$varRelatedObjectsColl}->setModel('{$objectClassname}');
}
";
  }

  public function addRemoveAllRelations($builder)
  {
    $refTableName = $this->middleTable->getPhpName();
    $pluralRefTableName = $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName());

    return "
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
 * Method called to associate multiple {$objectClassname} objects as Equal Nest $pluralRefTableName of this one
 *
 * @param      {$objectClassname}[] $pluralRefTableName The {$objectClassname} objects to set as Equal Nest $pluralRefTableName relation of the current object
 * @return     void
 * @throws     PropelException
 */
public function add$pluralRefTableName(\$$pluralRefTableName)
{
  foreach (\$$pluralRefTableName as \$a$pluralRefTableName) {
    \$this->add$refTableName(\$a$pluralRefTableName);
  }
}
";
  }

  public function removeObjectFromRelatedCollection($builder)
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
 * Method called to remove a {$objectClassname} object from the Equal Nest $pluralRefTableName relation
 *
 * @param      {$objectClassname} \$a$refTableName The {$objectClassname} object to remove as a $refTableName of the current object
 * @return     void
 * @throws     PropelException
 */
public function remove$refTableName({$objectClassname} \$a$refTableName)
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
