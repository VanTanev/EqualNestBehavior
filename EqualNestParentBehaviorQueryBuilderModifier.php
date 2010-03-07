<?php
  
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
  \$obj->clearList{$this->middle_table->getPhpName()}IDs();
  \$obj->clear{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}();
  
  return \$obj->get{$this->builder->getPluralizer()->getPluralForm($this->middle_table->getPhpName())}(\$this, \$con);
}    
";
  }
  
}