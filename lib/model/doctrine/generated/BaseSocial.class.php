<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseSocial extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('social');
    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

  public function setUp()
  {
    $relationshipcategorytemplate0 = new RelationshipCategoryTemplate();
    $this->actAs($relationshipcategorytemplate0);
  }
}