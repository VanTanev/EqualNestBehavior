<?php
class EqualNestParentBehaviorPeerBuilderModifier
{
  protected $behavior,  $builder, $objectClassname, $peerClassname;
  
  /** @var Table */
  protected $table;
  
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
    $tableName = $this->table->getName();
    /** @var Column */
    $pks = $this->table->getPrimaryKey();
    //$pks->getFullyQualifiedName())
    //$pks->getStudlyPhpName()
    throw new Exception($pks[0]->getFullyQualifiedName());
    //$pks->getFullyQualifiedName()
    //$pk_name = $pks[0]->getName();
    
    $middleTableName = $this->middle_table->getName();

    $ucMiddleTableName = strtoupper($middleTableName);
    
    $script = "
/**
 *
 */      
const LIST_EQUAL_NEST_{$ucMiddleTableName}_QUERY = '
  SELECT {$pks[0]->getFullyQualifiedName()} FROM child 
  INNER JOIN brothers ON 
    child.ID = brothers.CHILD1
    OR 
    child.ID = brothers.CHILD2
  WHERE 
    child.ID IN (
      SELECT brothers.CHILD1 FROM brothers WHERE brothers.child2 = :child_id
    )
    OR 
    child.id IN (
      SELECT brothers.child2 FROM brothers WHERE brothers.child1 = :child_id
    )
';

";

    return $script;  
  }
}