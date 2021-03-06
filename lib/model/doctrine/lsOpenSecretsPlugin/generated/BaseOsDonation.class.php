<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('OsDonation', 'raw');

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseOsDonation extends sfDoctrineRecord
{
  public function setTableDefinition()
  {
    $this->setTableName('os_donation');
    $this->hasColumn('cycle', 'string', 4, array('type' => 'string', 'primary' => true, 'length' => '4'));
    $this->hasColumn('row_id', 'string', 30, array('type' => 'string', 'primary' => true, 'length' => '30'));
    $this->hasColumn('donor_id', 'string', 12, array('type' => 'string', 'length' => '12'));
    $this->hasColumn('donor_name', 'string', 50, array('type' => 'string', 'length' => '50'));
    $this->hasColumn('recipient_id', 'string', 9, array('type' => 'string', 'length' => '9'));
    $this->hasColumn('employer_name', 'string', 50, array('type' => 'string', 'length' => '50'));
    $this->hasColumn('parent_name', 'string', 50, array('type' => 'string', 'length' => '50'));
    $this->hasColumn('industry_id', 'string', 5, array('type' => 'string', 'length' => '5'));
    $this->hasColumn('date', 'date', null, array('type' => 'date'));
    $this->hasColumn('amount', 'integer', null, array('type' => 'integer'));
    $this->hasColumn('street', 'string', 40, array('type' => 'string', 'length' => '40'));
    $this->hasColumn('city', 'string', 30, array('type' => 'string', 'length' => '30'));
    $this->hasColumn('state', 'string', 2, array('type' => 'string', 'length' => '2'));
    $this->hasColumn('zip', 'string', 5, array('type' => 'string', 'length' => '5'));
    $this->hasColumn('recipient_code', 'string', 2, array('type' => 'string', 'length' => '2'));
    $this->hasColumn('transaction_type', 'string', 3, array('type' => 'string', 'length' => '3'));
    $this->hasColumn('committee_id', 'string', 9, array('type' => 'string', 'length' => '9'));
    $this->hasColumn('intermediate_id', 'string', 9, array('type' => 'string', 'length' => '9'));
    $this->hasColumn('gender', 'string', 1, array('type' => 'string', 'length' => '1'));
    $this->hasColumn('employer_raw', 'string', 35, array('type' => 'string', 'length' => '35'));
    $this->hasColumn('fec_id', 'string', 11, array('type' => 'string', 'length' => '11'));
    $this->hasColumn('title_raw', 'string', 38, array('type' => 'string', 'length' => '38'));
    $this->hasColumn('org_raw', 'string', 38, array('type' => 'string', 'length' => '38'));
    $this->hasColumn('source', 'string', 5, array('type' => 'string', 'length' => '5'));
    $this->hasColumn('donor_name_last', 'string', 30, array('type' => 'string', 'length' => '30'));
    $this->hasColumn('donor_name_first', 'string', 20, array('type' => 'string', 'length' => '20'));
    $this->hasColumn('donor_name_middle', 'string', 20, array('type' => 'string', 'length' => '20'));
    $this->hasColumn('donor_name_suffix', 'string', 20, array('type' => 'string', 'length' => '20'));
    $this->hasColumn('donor_name_nick', 'string', 20, array('type' => 'string', 'length' => '20'));

    $this->option('collate', 'utf8_unicode_ci');
    $this->option('charset', 'utf8');
  }

}