<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Lobbyist extends BaseLobbyist
{


  public function getLobbyingClientsQuery()
  {
    $q = LsDoctrineQuery::create()
      ->from('Entity e')
      ->leftJoin('e.Relationship r ON (e.id = r.entity1_id)')
      ->leftJoin('r.LobbyFilingRelationship lfr')
      ->leftJoin('lfr.LobbyFiling lf')
      ->leftJoin('lf.LobbyFilingLobbyist lfl')
      ->where('lfl.lobbyist_id = ? AND r.category_id = ?', array($this->entity_id, RelationshipTable::TRANSACTION_CATEGORY));
      
    return $q;
  }
  
  public function getLobbyingFirmsQuery()
  {
    $q = LsDoctrineQuery::create()
      ->from('Entity e')
      ->leftJoin('e.Relationship r ON (e.id = r.entity2_id)')
      ->where('r.entity1_id = ?', $this->entity_id);
      
    return $q;
  }

}