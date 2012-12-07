<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseDomain extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('domain');
    $this->hasColumn('name', 'string', 40, array('type' => 'string', 'notnull' => true, 'notblank' => true, 'length' => '40'));
    $this->hasColumn('url', 'string', 200, array('type' => 'string', 'notnull' => true, 'notblank' => true, 'length' => '200'));

    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $this->hasMany('ExternalKey', array('local' => 'id',
                                        'foreign' => 'domain_id'));
  }
}