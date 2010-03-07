<?php
class EqualNestParentBehaviorObjectBuilderModifier
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
  
}