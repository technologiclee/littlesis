<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseExternalKey extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('external_key');
    $this->hasColumn('entity_id', 'integer', null, array('type' => 'integer', 'notnull' => true));
    $this->hasColumn('external_id', 'string', 200, array('type' => 'string', 'notblank' => true, 'notnull' => true, 'length' => '200'));
    $this->hasColumn('domain_id', 'integer', null, array('type' => 'integer', 'notnull' => true));


    $this->index('uniqueness', array('fields' => array(0 => 'external_id', 1 => 'domain_id'), 'type' => 'unique'));
    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $this->hasOne('Entity', array('local' => 'entity_id',
                                  'foreign' => 'id',
                                  'onDelete' => 'CASCADE',
                                  'onUpdate' => 'CASCADE'));

    $this->hasOne('Domain', array('local' => 'domain_id',
                                  'foreign' => 'id'));
  }
}