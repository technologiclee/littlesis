<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class LsListTable extends Doctrine_Table
{
  const US_NETWORK_ID = 79;
  const US_NETWORK_NAME = 'United States';

  static function generateRoute($list, $action=null, Array $params=null, $hideParams=false)
  {
    if (!is_array($list) && !($list instanceOf LsList))
    {
      throw new Exception("Can't get generate route; expecting LsList or array");
    }

    if ($action == 'view')
    {
      $action = null;
    }

    $paramStr = '';

    if ($params)
    {
      $params = array_diff_key($params, array_flip(array('id', 'slug', 'target', 'module', 'action')));
    
      $paramStr = http_build_query($params);
      $paramStr = !$hideParams && $paramStr ? '&' . $paramStr : null;
    }

    if ($action)
    {
      return '@list?id=' . $list['id'] . '&slug=' . LsSlug::convertNameToSlug($list['name']) . '&action=' . $action . $paramStr;
    }
    else
    {
      return '@listView?id=' . $list['id'] . '&slug=' . LsSlug::convertNameToSlug($list['name']) . $paramStr;
    }  
  }
  
  
  static function getInternalUrl($list, $action=null, Array $params=null, $hideParams=false)
  {
    return self::generateRoute($list, $action, $params, $hideParams);
  }
  
  
  static function getSimpleSearchQuery($terms)
  {
    $terms = (array) $terms;
    
    $q = LsDoctrineQuery::create()
        ->from('LsList l');
        
    foreach($terms as $term)
    {
      $q->addWhere('name like ?', '%' . $term . '%');
    }
    return $q;
  }  


  static function getLatestListEntityQuery($list)
  {
    $q = LsDoctrineQuery::create()
      ->from('LsListEntity le')
      ->leftJoin('le.Entity e')
      ->leftJoin('le.LsList l')
      ->where('le.id = ?', $list['id'])
      ->orderBy('created_at DESC')
      ->limit(1);
      
    return $q;
  }
  
  
  static function getEntityIdsById($id)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT entity_id FROM ls_list_entity le WHERE le.list_id = ? AND le.is_deleted = 0';
    $stmt = $db->execute($sql, array($id));
    
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }


  static function countPersons($id)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT COUNT(*) FROM ls_list_entity le LEFT JOIN entity e ON (e.id = le.entity_id) WHERE le.list_id = ? AND le.is_deleted = 0 AND e.primary_ext = ?';
    $stmt = $db->execute($sql, array($id, 'Person'));
    
    return $stmt->fetch(PDO::FETCH_COLUMN);
  }
  
  
  static function getUri($list)
  {
    if (!$list['id'] || !$list['name'])
    {
      return null;
    }

    return 'http://littlesis.org/list/' . $list['id'] . '/' . LsSlug::convertNameToSlug($list['name']);
  }
  
  
  static function getNetworksForSelect()
  {
    $choices = array(self::US_NETWORK_ID => self::US_NETWORK_NAME);
    $networks = LsDoctrineQuery::create()
      ->select('l.id, l.name')
      ->from('LsList l')
      ->where('l.id <> ? AND l.is_deleted = 0 AND l.is_network = 1', self::US_NETWORK_ID)
      ->fetchAll(PDO::FETCH_KEY_PAIR);

    return $choices + $networks;
  }
  

  static function getAllNetworkIds()
  {
    return array_keys(self::getNetworksForSelect());
  }

  
  static function getNameById($id)
  {
    return LsDoctrineQuery::create()
      ->select('l.name')
      ->from('LsList l')
      ->where('l.id = ?', $id)
      ->fetch(PDO::FETCH_COLUMN);
  }  


  static function getDisplayNameById($id)
  {
    return LsDoctrineQuery::create()
      ->select('l.display_name')
      ->from('LsList l')
      ->where('l.id = ?', $id)
      ->fetch(PDO::FETCH_COLUMN);
  }  
  
  
  static function getNetworkByDisplayName($name)
  {
    return LsDoctrineQuery::create()
      ->select('l.*')
      ->from('LsList l')
      ->where('l.display_name = ?', $name)
      ->andWhere('l.is_network = 1')
      ->fetchOne();
  }


  static function getNetworkInternalUrl($network, $action=null, Array $params=null, $hideParams=false)
  {
    if (!is_array($network) && !($network instanceOf LsList))
    {
      throw new Exception("Can't get generate route; expecting LsList or array");
    }

    if ($action == 'view')
    {
      $action = null;
    }

    $paramStr = '';

    if ($params)
    {
      $params = array_diff_key($params, array_flip(array('id', 'slug', 'target', 'module', 'action')));
    
      $paramStr = http_build_query($params);
      $paramStr = !$hideParams && $paramStr ? '&' . $paramStr : null;
    }

    if ($action)
    {
      return '@local?name=' . $network['display_name'] . '&action=' . $action . $paramStr;
    }
    else
    {
      return '@localView?name=' . $network['display_name'] . $paramStr;
    }  
  }
}