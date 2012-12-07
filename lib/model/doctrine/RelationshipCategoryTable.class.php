<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class RelationshipCategoryTable extends Doctrine_Table
{
  static $categoryNames = array(
    1 => 'Position',
    2 => 'Education',
    3 => 'Membership',
    4 => 'Family',
    5 => 'Donation',
    6 => 'Transaction',
    7 => 'Lobbying',
    8 => 'Social',
    9 => 'Professional',
    10 => 'Ownership'
  );


  static $categoryDisplayNames = array(
    1 => 'Position',
    2 => 'Education',
    3 => 'Membership',
    4 => 'Family',
    5 => 'Donation/Grant',
    6 => 'Service/Transaction',
    7 => 'Lobbying',
    8 => 'Social',
    9 => 'Professional',
    10 => 'Ownership'
  );
  

  static $categoryDefaultDescriptions = array(
    1 => 'Position',
    2 => 'Student',
    3 => 'Member',
    4 => 'Relative',
    5 => 'Donation/Grant',
    6 => 'Service/Transaction',
    7 => 'Lobbying',
    8 => 'Social',
    9 => 'Professional',
    10 => 'Owner'
  );
  
  
  static $categoryFields = array(
    1 => array('is_board', 'is_executive', 'is_employee', 'compensation', 'boss_id'),
    2 => array('degree_id', 'field', 'is_dropout'),
    3 => array('dues'),
    4 => array(),
    5 => array('bundler_id'),
    6 => array('contact1_id', 'contact2_id', 'district_id', 'is_lobbying'),
    7 => array(),
    8 => array(),
    9 => array(),
    10 => array('percent_stake', 'shares')
  );


  static function getNameById($id)
  {
    if (isset(self::$categoryNames[$id]))
    {
      return self::$categoryNames[$id];
    }
    
    return null;
  }


  static function getDisplayNameById($id)
  {
    if (isset(self::$categoryDisplayNames[$id]))
    {
      return self::$categoryDisplayNames[$id];
    }
    
    return null;
  }
  

  static function getDefaultDescriptionById($id)
  {
    if (isset(self::$categoryDefaultDescriptions[$id]))
    {
      return self::$categoryDefaultDescriptions[$id];
    }
    
    return null;  
  }


  static function getByExtensionsQuery($ext1, $ext2, $orderMatters=false)
  {
    $q = LsDoctrineQuery::create()
      ->from('RelationshipCategory c');
  
    if ($orderMatters)
    {
      $q->addWhere('c.entity1_requirements IS NULL OR c.entity1_requirements = ?', $ext1)
        ->addWhere('c.entity2_requirements IS NULL OR c.entity2_requirements = ?', $ext2);    
    }
    else
    {
      $q->addWhere('( (c.entity1_requirements IS NULL OR c.entity1_requirements = ?) AND (c.entity2_requirements IS NULL OR c.entity2_requirements = ?) ) OR ( (c.entity1_requirements IS NULL OR c.entity1_requirements = ?) AND (c.entity2_requirements IS NULL OR c.entity2_requirements = ?) )', array($ext1, $ext2, $ext2, $ext1));
    }
  
    return $q;
  }
}