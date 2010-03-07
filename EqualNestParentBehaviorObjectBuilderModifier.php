<?php
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
 * @var        array {$objectClassname}[] Collection to store Equal Nest {$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())} of this $objectClassname
 */
protected \$collEqualNest{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())};
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
  if (null !== \$this->collEqualNest$pluralRefTableName) {
    \$this->clearList{$pluralRefTableName}PKs();
    \$this->initList{$pluralRefTableName}PKs(\$con);
  
    \$con->beginTransaction();
    try {

      foreach (\$this->get$pluralRefTableName()->getPrimaryKeys() as \$pk)
      {
        if (!in_array(\$pk, \$this->listEqualNest{$pluralRefTableName}PKs)) {
          // save new equal nest relation
          $refPeerClassname::build{$refTableName}Relation(\$this, \$pk, \$con);
        } else {
          // remove the pk from the list of db keys
          unset(\$this->listEqualNest{$pluralRefTableName}PKs[\$pk]);
        }
      }
      
      // if we have keys still left, this means they are relations that have to be removed
      foreach (\$this->listEqualNest{$pluralRefTableName}PKs as \$old_pk)
      {
        $refPeerClassname::remove{$refTableName}Relation(\$this, \$old_pk, \$con);
      }

      \$con->commit();
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
      \$stmt = \$con->prepare(ChildPeer::LIST_EQUAL_NEST_{$ucMiddleTableName}_PKs_QUERY);
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
 * @see        removeAll$pluralRefTableName() 
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
 * Gets an array of Child objects which are Equal Nest $pluralRefTableName of this object.
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
public function get$pluralRefTableName(\$criteria = null, PropelPDO \$con = null)
{
  if (null === \$this->$varListRelatedPKs) {
    \$this->initList{$refTableName}PKs(\$con);
  }
  
  if (null === \$this->$varRelatedObjectsColl || null !== \$criteria) {
    if (\$this->isNew() && null === \$this->$varRelatedObjectsColl) {
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
    
";
  }    
  
  
}