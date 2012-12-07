<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Lobbying extends BaseLobbying
{
  public function getDetails()
  {
    $stuff = array();

    if ($amount = LsNumber::makeReadable($this->Relationship->amount, '$'))
    {
      $stuff[] = $amount;
    }
    
    if ($this->Relationship->filings)
    {
      $stuff[] = $this->Relationship->filings . ' filings';
    }
    
    return implode(', ', $stuff);
  }


  static function getLobbyFilingsByRelationshipIdQuery($relationshipId)
  {
    return LsDoctrineQuery::create()
      ->from('LobbyFiling f')
      ->leftJoin('f.LobbyFilingRelationship r')
      ->where('r.relationship_id = ?', $relationshipId)
      ->orderBy('f.start_date DESC');
  }


  public function getLobbyFilingsQuery()
  {
    return self::getLobbyFilingsByRelationshipIdQuery($this->relationship_id);
  }


  public function countLobbyFilings()
  {
    return $this->getLobbyFilingsQuery()->count();
  }


  static function getLobbyFilingAmountSumByRelationshipId($id)
  {
    $ary = LsDoctrineQuery::create()
      ->select('SUM(f.amount) AS sum')
      ->from('LobbyFiling f')
      ->where('f.relationship_id = ?', $id)
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
      ->fetchOne();

    return (int) $ary['sum'];
  }


  public function getLobbyFilingAmountSum()
  {
    return self::getLobbyFilingAmountSumByRelationshipId($this->relationship_id);
  }

  
  static function addLobbyFilingToRelationship(Relationship $relationship, LobbyFiling $filing)
  {  
    $generatedAmount = self::getFecFilingAmountSumByRelationshipId($relationship->id) + $filing->amount;
    $relationship->amount = max($generatedAmount, $relationship->amount);
    $relationship->updateDateRange($filing->start_date);
    $relationship->filings = $this->getLobbyFilingsByRelationshipId($relationship->id)->count() + 1;
    $relationship->save();

    $filing->relationship_id =  $relationship->id;    
    $filing->save();
  }


  public function addLobbyFiling(LobbyFiling $filing)
  {
    return self::addLobbyFilingToRelationship($this->Relationship);
  }


  public function getTransactionsFromLobbyFilingQuery()
  {
    $q = LsDoctrineQuery::create()
      ->from('Relationship r')
      ->leftJoin('r.LobbyFilingRelationship lfr')
      ->leftJoin('lfr.LobbyFiling lf')
      ->leftJoin('lf.LobbyFilingRelationship lfr2')
      ->andWhere('lfr2.relationship_id = ?', $this->relationship_id)
      ->addWhere('r.id <> ?', $this->relationship_id)
      ->addWhere('r.category_id = ?', RelationshipTable::TRANSACTION_CATEGORY);
    
    return $q;
  }
  
  
  public function getTransactionsFromLobbyFiling()
  {
    return $this->getTransactionsFromLobbyFilingQuery()->execute();
  }
}