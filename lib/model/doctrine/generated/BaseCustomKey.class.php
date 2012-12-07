<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseCustomKey extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('custom_key');
    $this->hasColumn('name', 'string', 50, array('type' => 'string', 'notnull' => true, 'notblank' => true, 'length' => '50'));
    $this->hasColumn('value', 'clob', null, array('type' => 'clob'));
    $this->hasColumn('description', 'string', 200, array('type' => 'string', 'length' => '200'));

    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $timestampable0 = new Doctrine_Template_Timestampable();
    $objectable0 = new Objectable();
    $referenceable0 = new Referenceable();
    $this->actAs($timestampable0);
    $this->actAs($objectable0);
    $this->actAs($referenceable0);
  }
}