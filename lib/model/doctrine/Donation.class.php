<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Donation extends BaseDonation
{
  public function getDetails()
  {
    $details = array();

    if ($this->Relationship->amount)
    {
      $details[] = '$' . $this->Relationship->amount;
    }
    
    if ($this->Relationship->goods)
    {
      $details[] = $this->Relationship->goods;
    }
    
    return implode(', ', $details);
  }
  

  public function getFecFilingAmountSum()
  {
    $ary = LsDoctrineQuery::create()
      ->select('SUM(f.amount) AS sum')
      ->from('FecFiling f')
      ->where('f.relationship_id = ?', $this->relationship_id)
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
      ->fetchOne();

    return (int) $ary['sum'];
  }

  
  public function addFecFiling(FecFiling $filing)
  {  
    $relationship = $this->Relationship;
    $generatedAmount = $this->getFecFilingAmountSum() + $filing->amount;
    $relationship->amount = max($generatedAmount, $relationship->amount);
    $relationship->updateDateRange($filing->start_date);
    $relationship->filings = $this->countFecFilings() + 1;
    $relationship->save();

    $filing->relationship_id =  $relationship->id;    
    $filing->save();    
  }


  public function removeFecFiling(FecFiling $filing)
  {
    if ($filing->relationship_id != $this->relationship_id)
    {
      throw new Exception("Can't remove FecFiling that doesn't belong to this Donation");
    }

    $relationship = $this->Relationship;
    
    if ($relationship->amount == $this->getFecFilingAmountSum())
    {
      $relationship->amount -= $filing->amount;
    }
    
    $relationship->filings = $this->countFecFilings() - 1;
    
    $relationship->save();    
    $filing->delete();    
  }

  
  public function getFecFilingsQuery()
  {
    return LsDoctrineQuery::create()
      ->from('FecFiling f')
      ->where('f.relationship_id = ?', $this->relationship_id)
      ->orderBy('f.start_date DESC');
  }
  
  
  public function countFecFilings()
  {
    return $this->getFecFilingsQuery()->count();
  }
  
  
  public function updateFromFecFilings()
  {
    $rel = $this->Relationship;
    
    //update filing number
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT SUM(amount) amount, COUNT(id) filings, MIN(start_date) start_date, MAX(end_date) end_date FROM fec_filing WHERE relationship_id = ?';
    $stmt = $db->execute($sql, array($rel->id));
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result['filings'])
    {    
      return false;
    }

    $rel->amount = $result['amount'];
    $rel->filings = $result['filings'];
    $rel->start_date = substr($result['start_date'], 0, 4) . '-00-00';
    $rel->end_date = substr($result['end_date'], 0, 4) . '-00-00';
    $rel->save();
    
    return true;
  }
  
  
  static function updateRelationshipFromFecFilings($id)
  {
    $db = Doctrine_Manager::connection();

    //get filing totals for each relationship
    $sql = 'SELECT SUM(amount) amount, COUNT(id) filings, MIN(start_date) start_date, MAX(end_date) end_date FROM fec_filing WHERE relationship_id = ?';
    $stmt = $db->execute($sql, array($id));    
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$total['filings'])
    {    
      //if no filings, soft-delete the relationship
      LsDoctrineQuery::create()
        ->delete()
        ->from('FecFiling f')
        ->where('f.id = ?', $id)
        ->execute();
    }

    $sql = 'UPDATE relationship SET amount = ?, filings = ?, start_date = ?, end_date = ? WHERE id = ?';
    $stmt = $db->execute($sql, array(
      $total['amount'], 
      $total['filings'],
      substr($total['start_date'], 0, 4) . '-00-00',
      substr($total['end_date'], 0, 4) . '-00-00'
    ));    
  }
}