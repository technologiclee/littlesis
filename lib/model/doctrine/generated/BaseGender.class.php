<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseGender extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('gender');
    $this->hasColumn('name', 'string', 10, array('type' => 'string', 'notnull' => true, 'length' => '10'));

    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $this->hasMany('Person', array('local' => 'id',
                                   'foreign' => 'gender_id'));
  }
}