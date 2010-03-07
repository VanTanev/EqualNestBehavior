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
    \$this->clearList{$pluralRefTableName}IDs();
    \$this->initList{$pluralRefTableName}IDs(\$con);
  
    \$con->beginTransaction();
    try {

      foreach (\$this->get$pluralRefTableName()->getPrimaryKeys() as \$pk)
      {
        if (!in_array(\$pk, \$this->listEqualNest{$pluralRefTableName}IDs)) {
          // save new equal nest relation
          $refPeerClassname::build{$refTableName}Relation(\$this, \$pk, \$con);
        } else {
          // remove the pk from the list of db keys
          unset(\$this->listEqualNest{$pluralRefTableName}IDs[\$pk]);
        }
      }
      
      // if we have keys still left, this means they are relations that have to be removed
      foreach (\$this->listEqualNest{$pluralRefTableName}IDs as \$old_pk)
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
  
}