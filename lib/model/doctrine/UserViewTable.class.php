<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class UserViewTable extends Doctrine_Table
{
  static function logView(Doctrine_Record $record)
  {
    $user = sfContext::getInstance()->getUser();

    if (!sfConfig::get('app_logging_views') || !$user->isAuthenticated())
    {
      return;
    }

    if (!$record->exists())
    {
      throw new Exception("Can't log user view for new record");
    }


    $view = new UserView;
    $view->setObject($record);
    $view->User = $user->getGuardUser();
    $view->save();
  }
  
  
  static function getViewsByUserIdAndModelsQuery($user_id, Array $models=null, $groupEntities=false)
  {
    $q = LsDoctrineQuery::create()
      ->from('UserView v')
      ->where('v.user_id = ? AND v.is_visible = ?', array($user_id, true))
      ->orderBy('v.created_at DESC');
      
    if ($models)
    {
      $q->andWhereIn('v.object_model', $models);
    }
      
    if ($groupEntities)
    {
      $q->groupBy('v.object_model, v.object_id')
        ->orderBy('MAX(v.created_at) DESC');
    }
    
    return $q;
  }
}