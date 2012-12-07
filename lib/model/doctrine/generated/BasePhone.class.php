<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BasePhone extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('phone');
    $this->hasColumn('entity_id', 'integer', null, array('type' => 'integer', 'notnull' => true));
    $this->hasColumn('number', 'string', 20, array('type' => 'string', 'notnull' => true, 'length' => '20'));
    $this->hasColumn('type', 'enum', null, array('type' => 'enum', 'values' => PhoneTable::$types));

    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $this->hasOne('Entity', array('local' => 'entity_id',
                                  'foreign' => 'id',
                                  'onDelete' => 'CASCADE',
                                  'onUpdate' => 'CASCADE'));

    $timestampable0 = new Doctrine_Template_Timestampable();
    $referenceable0 = new Referenceable();
    $lsversionable0 = new LsVersionable();
    $softdelete0 = new Doctrine_Template_SoftDelete(array('name' => 'is_deleted'));
    $this->actAs($timestampable0);
    $this->actAs($referenceable0);
    $this->actAs($lsversionable0);
    $this->actAs($softdelete0);
  }
}