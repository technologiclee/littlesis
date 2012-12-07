<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class LobbyFilingTable extends Doctrine_Table
{
  static function getAgenciesQuery($filing)
  {
    $q = LsDoctrineQuery::create()
      ->from('Entity e')
      ->leftJoin('e.Relationship r ON (e.id = r.entity2_id)')
      ->leftJoin('r.LobbyFilingRelationship lfr')
      ->where('lfr.lobby_filing_id = ? AND r.category_id = ?', array($filing['id'], RelationshipTable::LOBBYING_CATEGORY));
      
    return $q;
  }


  static function getSourceUrl($filing)
  {
    if (!$federalId = $filing['federal_filing_id'])
    {
      return null;
    }

    return LobbyingScraper::$filing_url . $federalId;
  }
}