<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Email extends BaseEmail
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
  
  
  public function __toString()
  {    
    return $this->address;
  }
}