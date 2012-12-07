<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class RelationshipTable extends Doctrine_Table
{
  const POSITION_CATEGORY = 1;
  const EDUCATION_CATEGORY = 2;
  const MEMBERSHIP_CATEGORY = 3;
  const FAMILY_CATEGORY = 4;
  const DONATION_CATEGORY = 5;
  const TRANSACTION_CATEGORY = 6;
  const LOBBYING_CATEGORY = 7;
  const SOCIAL_CATEGORY = 8;
  const PROFESSIONAL_CATEGORY = 9;
  const OWNERSHIP_CATEGORY = 10;


  static $categories = array(
    'Position', 
    'Education',
    'Membership',
    'Family',
    'Donation',
    'Transaction',
    'Lobbying',
    'Social',
    'Professional',
    'Ownership'
  );
  
  static $prepositions = array(
    "",
    "of",
    "at",
    "of",
    "of",
    "to",
    "for",
    "for",
    "of",
    "of",
    "of"  
  );

  const DEFAULT_PREPOSITION = "&rarr;";
  
  static $advanced_prepositions = array(
    1 => array(1 => "at", 2 => self::DEFAULT_PREPOSITION),
    2 => array(1 => "at", 2 => self::DEFAULT_PREPOSITION),
    3 => array(1 => "of", 2 => self::DEFAULT_PREPOSITION),
    4 => array(1 => "of", 2 => "of"),
    5 => array(1 => "to", 2 => "from"),
    6 => array(1 => "of", 2 => "of"),
    7 => array(1 => "for", 2 => "by"),
    8 => array(1 => "of", 2 => "of"),
    9 => array(1 => "of", 2 => "of"),
    10 => array(1 => "of", 2 => self::DEFAULT_PREPOSITION)
  );


  static function getAllCategoryIds()
  {
    return range(1, 10);
  }
  
  static function getByCategoryQuery($category)
  {
    if (!in_array($category, self::$categories))
    {
      throw new Exception("Invalid relationship category: " . $category);
    }  
    
    $lower = strtolower($category);
    
    return LsDoctrineQuery::create()
      ->from('Relationship r')
      ->where('r.category_id = ?', constant('self::' . strtoupper($category) . '_CATEGORY'))
      ->leftJoin('r.' . $category . ' ' . $lower);
  }


  static function getDescriptionsByCategoryId($categoryId, $includeBlank=true)
  {
    $descriptions = $includeBlank ? array('') : array();

    $db = Doctrine_Manager::connection();
    $sql = 'SELECT description1, description2 FROM relationship WHERE category_id = ?';
    $stmt = $db->execute($sql, array($categoryId));

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
    {
      $descriptions[] = $row['description1'];
      $descriptions[] = $row['description2'];
    }
    
    $descriptions = array_unique($descriptions);
    sort($descriptions);
    $descriptions = array_combine($descriptions, $descriptions);

    return $descriptions;
  }
  
  
  static function getDescriptionsByText($description, $categoryId=null)
  {
    $descriptions = array();
      
    $q = LsDoctrineQuery::create()
      ->select('r.description1, r.description2')
      ->from('Relationship r')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);

    if ($categoryId)
    {
      $q->addWhere('r.category_id = ?', $categoryId);
    }
    
    $looseRows = $q->where('r.description1 LIKE ? OR r.description2 LIKE ?', array('%' . $description . '%', '%' . $description . '%'))
                   ->limit(10)
                   ->execute();

    $strictRows = $q->where('r.description1 LIKE ? OR r.description2 LIKE ?', array($description . '%', $description . '%'))
                    ->limit(10)
                    ->execute();

    $rows = array_merge($strictRows, $looseRows);
    
    foreach ($rows as $row)
    {
      if (stristr($row['description1'], $description))
      {
        $descriptions[] = $row['description1'];
      }
            
      if (stristr($row['description2'], $description))
      {
        $descriptions[] = $row['description2'];      
      }
    }
    
    sort($descriptions);
    
    $startMatches = array();
    
    foreach ($descriptions as $desc)
    {
      if (strpos($desc, $description) === 0)
      {
        $startMatches[] = $desc;
      }
    }

    $descriptions = array_merge($startMatches, $descriptions);
    $descriptions = array_unique($descriptions);
    
    return $descriptions;    
  }
  
  
  static function getBetweenEntityAndGroupQuery($entity, Array $entityIds, $categoryIds=null, $order=null)
  {
    $q = LsDoctrineQuery::create()
      ->select('r.*, e1.*, e2.*')
      ->from('Relationship r')
      ->leftJoin('r.Entity1 e1')
      ->leftJoin('r.Entity2 e2')
      ->orderBy('r.start_date DESC, r.end_date DESC');

    if ($order == 1)
    {
      $q->addWhere('r.entity1_id = ?', $entity['id'])
        ->andWhereIn('r.entity2_id', $entityIds);
    }
    elseif ($order == 2)
    {
      $q->addWhere('r.entity2_id = ?', $entity['id'])
        ->andWhereIn('r.entity1_id', $entityIds);
    }
    else
    {
      $q->addWhere(
       '(r.entity1_id = ? AND r.entity2_id IN(' . implode(',', $entityIds) . ')) OR (r.entity1_id IN(' . implode(',', $entityIds) . ') AND r.entity2_id = ?)',
       array($entity['id'], $entity['id'])
      );
    
    }
    
    if ($categoryIds)
    { 
      $q->andWhereIn('r.category_id', (array) $categoryIds);
    }
    
    return $q;  
  }
  
  
  static function getCategoryNameByFieldName($fieldName)
  {
    foreach (self::$categories as $category)
    {
      $table = Doctrine::getTable($category);

      if (in_array($fieldName, array_keys($table->getColumns())))
      {
        return $category;
      }    
    }  
    
    return null;  
  }


  static function generateRoute($relationship, $action=null, Array $params=null, $hideParams=false)
  {
    if (!is_array($relationship) && !($relationship instanceOf Relationship))
    {
      throw new Exception("Can't get generate route; expecting Relationship or array");
    }

    if (!$action)
    {
      $action = 'view';
    }

    if ($relationship['is_deleted'] && $action == 'view')
    {
      $action = 'modifications';
    }

    $paramStr = '';

    if ($params)
    {
      $params = array_diff_key($params, array_flip(array('id', 'module', 'action')));
    
      $paramStr = http_build_query($params);
      $paramStr = !$hideParams && $paramStr ? '&' . $paramStr : null;
    }

    return '@relationship?action=' . $action . '&id=' . $relationship['id'] . $paramStr;
  }
  
  
  static function getDisplayDescription($rel, $useDefault=true)
  {
    if ($categoryName = RelationshipCategoryTable::getNameById($rel['category_id']))
    {
      $tableClass = $categoryName . 'Table';

      if (method_exists($tableClass, 'getDisplayDescription'))
      {
        if ($desc = call_user_func(array($tableClass, 'getDisplayDescription'), $rel))
        {
          return $desc;
        }
      }
    }
 
    return $useDefault ? RelationshipCategoryTable::getDefaultDescriptionById($rel['category_id']) : null;  
  }
  
  
  static function getCategoryDefaultDescription($rel)
  {
    if ($name = RelationshipCategoryTable::getDefaultDescriptionById($rel['category_id']))
    {
      return $name;
    }

    return null;  
  }  


  static function getName($rel)
  {
    //return generic name if components are missing
    if (!$rel['Entity1']['name'] || !$rel['Entity2']['name'] || !$rel['category_id'])
    {
      $ret = 'Relationship';
    }

    $ret = RelationshipCategoryTable::getNameById($rel['category_id']) . ': ' . $rel['Entity1']['name'] . ', ' . $rel['Entity2']['name'];

    if ($rel['is_deleted'])
    {
      $ret .= ' [deleted]';
    }
    
    return $ret;  
  }
  
  
  static function getReferencesForRelationship($rel)
  {
    if ((!$entity1 = $rel['Entity1']) || (!$entity2 = $rel['Entity2']))
    {
      throw new Exception("Can't get refeferences for relationship; entities must be set");
    }

    $refs = array();
    
    //get refs for relationship
    if ($rel['id'])
    {
      $refs = Referenceable::getReferencesByFieldsQuery($rel)->setHydrationMode(Doctrine::HYDRATE_ARRAY)->execute();
    }

    //get refs for entities
    $q = LsDoctrineQuery::create()
      ->from('Reference r')
      ->where('r.object_model = ? AND r.object_id = ?', array('Entity', $entity1['id']))
      ->orWhere('r.object_model = ? AND r.object_id = ?', array('Entity', $entity2['id']))
      ->groupBy('r.source, r.name')
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY);
      
    $refs = array_merge($refs, $q->execute());
    
    //get 5 most recent refs for each entity1 relationship
    $refs = array_merge($refs, 
      EntityTable::getRecentRelationshipReferencesQuery($entity1, 5)
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
        ->execute()
    );

    //get 5 most recent refs for each entity2 relationship
    $refs = array_merge($refs, 
      EntityTable::getRecentRelationshipReferencesQuery($entity2, 5)
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
        ->execute()
    );
    
    return $refs;
  }
  
  
  static function getInternalUrl($relationship, $action=null, Array $params=null, $hideParams=false)
  {
    return self::generateRoute($relationship, $action, $params, $hideParams);
  }
  
  
  static function getNameByRelationshipAndEntities($relationship, $entity1, $entity2)
  {
    //return generic name if components are missing
    if (!$entity1['name'] || !$entity2['name'] || !$relationship['category_id'])
    {
      return 'Relationship';
    }

    $categoryName = RelationshipCategoryTable::getNameById($relationship['category_id']);

    return $categoryName . ': ' . $entity1['name'] . ', ' . $entity2['name'];
  }
  
  
  static function areSameDescriptions($relationship)
  {
    if ($relationship['description1'] && ($relationship['description1'] == $relationship['description2']))
    {
      return true;
    }
    
    return false;  
  }


  static function getFecFilingsByIdQuery($id)
  {
    return LsDoctrineQuery::create()
      ->from('FecFiling f')
      ->where('f.relationship_id = ?', $id)
      ->orderBy('f.start_date DESC');
  }
  
  
  static function getDegreeNameById($id)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT d.name FROM education e LEFT JOIN degree d ON (e.degree_id = d.id) WHERE e.relationship_id = ?';
    $stmt = $db->execute($sql, array($id));
    return $stmt->fetch(PDO::FETCH_COLUMN);
  }


  static function getTransactionsFromLobbyFilingByIdQuery($id)
  {
    $q = LsDoctrineQuery::create()
      ->from('Relationship r')
      ->leftJoin('r.LobbyFilingRelationship lfr')
      ->leftJoin('lfr.LobbyFiling lf')
      ->leftJoin('lf.LobbyFilingRelationship lfr2')
      ->andWhere('lfr2.relationship_id = ?', $id)
      ->addWhere('r.id <> ?', $id)
      ->addWhere('r.category_id = ?', RelationshipTable::TRANSACTION_CATEGORY);
    
    return $q;
  }


  static function getLobbyingsFromLobbyFilingById($id)
  {
    $q = LsDoctrineQuery::create()
      ->from('Relationship r')
      ->leftJoin('r.LobbyFilingRelationship lfr')
      ->leftJoin('lfr.LobbyFiling lf')
      ->leftJoin('lf.LobbyFilingRelationship lfr2')
      ->andWhere('lfr2.relationship_id = ?', $id)
      ->addWhere('r.id <> ?', $id)
      ->addWhere('r.category_id = ?', RelationshipTable::LOBBYING_CATEGORY);
    
    return $q->execute();
  }


  static function getFedspendingFilingsByIdQuery($id)
  {          
    return Doctrine_Query::create()
      ->from('FedspendingFiling f')
      ->where('f.relationship_id = ?', $id)
      ->orderBy('f.start_date DESC');    
  }


  static function getUri($id)
  {
    return $id ? 'http://littlesis.org/relationship/view/id/' . $id : null;
  }
  
  
  static function generateMetaDescription($rel)
  {
    if (@!$rel['Entity1']['name'] || @!$rel['Entity2']['name'])
    {
      return null;
    }
    
    return 'Information about ' . $rel['Entity1']['name'] . ', ' . $rel['Entity2']['name'] . ', and the connections between them.';
  }


  static function createLinksFromRelationship($rel)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'INSERT INTO link (entity1_id, entity2_id, category_id, relationship_id, is_reverse) VALUES(?, ?, ?, ?, ?), (?, ?, ?, ?, ?)';
    $stmt = $db->execute($sql, array($rel['entity1_id'], $rel['entity2_id'], $rel['category_id'], $rel['id'], false, $rel['entity2_id'], $rel['entity1_id'], $rel['category_id'], $rel['id'], true));  
  }


  static function deleteLinksByRelationshipId($id)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'DELETE FROM link WHERE relationship_id = ?';
    $stmt = $db->execute($sql, array($id));
  }


  static function updateLinksFromRelationship($rel)
  {
    $db = Doctrine_Manager::connection();      
    $sql = 'UPDATE link SET entity1_id = ?, entity2_id = ?, category_id = ? WHERE relationship_id = ? AND is_reverse = ?';
    $stmt = $db->execute($sql, array($rel['entity1_id'], $rel['entity2_id'], $rel['category_id'], $rel['id'], 0));
    $stmt = $db->execute($sql, array($rel['entity2_id'], $rel['entity1_id'], $rel['category_id'], $rel['id'], 1));
  }
  
  
  static function getPreposition($rel, $order = 1)
  {
    if ($rel["category_id"] == 5 && strpos("Campaign Committee", $rel['description1']) !== FALSE)
    {
      return self::DEFAULT_PREPOSITION;
    }

    return self::$advanced_prepositions[$rel["category_id"]][$order];
  }  
  
  static function formatSentenceForShare($str)
  {
    return trim(str_replace(array("\r", "\r\n", "\n"), '', strip_tags(get_slot('share_text'))));
  }
}