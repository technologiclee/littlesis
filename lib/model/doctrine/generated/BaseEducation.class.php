<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseEducation extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('education');
    $this->hasColumn('degree_id', 'integer', null, array('type' => 'integer'));
    $this->hasColumn('field', 'string', 30, array('type' => 'string', 'length' => '30'));
    $this->hasColumn('is_dropout', 'boolean', null, array('type' => 'boolean'));

    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $this->hasOne('Degree', array('local' => 'degree_id',
                                  'foreign' => 'id',
                                  'onDelete' => 'SET NULL',
                                  'onUpdate' => 'CASCADE'));

    $this->hasMany('Relationship', array('local' => 'relationship_id',
                                         'foreign' => 'id'));

    $relationshipcategorytemplate0 = new RelationshipCategoryTemplate();
    $this->actAs($relationshipcategorytemplate0);
  }
}