<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Alias extends BaseAlias
{

  protected $_isMerge = false;

  public function setMerge($bool)
  {
    $this->_isMerge = (bool) $bool;
  }
  

  public function isMerge()
  {
    return $this->_isMerge;
  }
  
  public function save(Doctrine_Connection $conn=null, $setEntityName=true)
  {
    if ($conn === null)
    {
      $conn = Doctrine_Manager::connection();
    }

    if ($setEntityName && $this->is_primary)
    {
      if ($this->Entity->name != $this->name)
      {
        $this->Entity->setEntityField('name', $this->name);
        $this->Entity->save();
      }
    }

    parent::save($conn);
  }
  
  
  public function makePrimary()
  {
    $db = Doctrine_Manager::connection();

    try
    {
      $db->beginTransaction();
      
      if ($currentPrimary = $this->Entity->getPrimaryAlias())
      {
        $currentPrimary->is_primary = false;
        $currentPrimary->save();
      }
      
      $this->is_primary = true;
      $this->save();

      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollback();
      throw $e;
    }
  }
}