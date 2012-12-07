<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BasePosition extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('position');
    $this->hasColumn('is_board', 'boolean', null, array('type' => 'boolean'));
    $this->hasColumn('is_executive', 'boolean', null, array('type' => 'boolean'));
    $this->hasColumn('is_employee', 'boolean', null, array('type' => 'boolean'));
    $this->hasColumn('compensation', 'integer', null, array('type' => 'integer'));
    $this->hasColumn('boss_id', 'integer', null, array('type' => 'integer'));

    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $this->hasOne('Entity as Boss', array('local' => 'boss_id',
                                          'foreign' => 'id',
                                          'onDelete' => 'SET NULL',
                                          'onUpdate' => 'CASCADE'));

    $this->hasMany('Relationship', array('local' => 'relationship_id',
                                         'foreign' => 'id'));

    $relationshipcategorytemplate0 = new RelationshipCategoryTemplate();
    $this->actAs($relationshipcategorytemplate0);
  }
}