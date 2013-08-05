<?php

class mapActions extends LsApiActions
{
  public function preExecute()
  {
    $this->allowCrossDomainRequest();
  }

  public function executeEntities($request)
  {
    $this->setResponseFormat();    

    $entity_ids = explode(",", $request->getParameter('entity_ids'));
    $this->data = EntityTable::getEntitiesAndRelsForMap($entity_ids);

    if ($request->getParameter('format') == "json")
    {
      return $this->renderText(json_encode($this->data));
    }
    else
    {
      return 'Xml';
    }
  }

  public function executeView($request)
  {
    $this->setResponseFormat();    

    $map = Doctrine::getTable("NetworkMap")->find($request->getParameter("id"));
    $this->forward404Unless($map);

    $response = $map->toArray();
    $response["data"] = json_decode($response["data"]);

    return $this->renderText(json_encode($response));
  }
  
  public function executeCreate($request)
  {
    if ($request->isMethod('post'))
    {
      $this->setResponseFormat();    

      $data = $request->getParameter("data");
      $decoded = json_decode($data);

      $map = new NetworkMap();
      $map->user_id = $request->getParameter("user_id");
      $map->data = $data;
      $map->entity_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->entities)));
      $map->rel_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->rels)));
      $map->save();

      $response = $map->toArray();
      $response["data"] = json_decode($response["data"]);
      
      return $this->renderText(json_encode($response));
    }  
    
    //404 Not Found
    $this->returnStatusCode(400);
  }  

  public function executeUpdate($request)
  {
    if ($request->isMethod('post'))
    {
      $this->setResponseFormat();    

      $map = Doctrine::getTable("NetworkMap")->find($request->getParameter("id"));
      $this->forward404Unless($map);

      $data = $request->getParameter("data");
      $decoded = json_decode($data);

      $map->data = $data;
      $map->entity_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->entities)));
      $map->rel_ids = implode(",", array_values(array_map(function($e) { return $e->id; }, $decoded->rels)));
      $map->save();

      $response = $map->toArray();
      $response["data"] = json_decode($response["data"]);

      return $this->renderText(json_encode($response));
    }  
    
    //404 Not Found
    $this->returnStatusCode(400);
  }
  
  public function executeAddEntityData($request)
  {
    $this->setResponseFormat();    

    $entity_id = $request->getParameter("entity_id");
    $entity_ids = explode(",", $request->getParameter("entity_ids"));

    $data = EntityTable::getAddEntityAndRelsForMap($entity_id, $entity_ids);    

    return $this->renderText(json_encode($data));
  }
  
  public function executeSearchEntities($request)
  {
    $this->setResponseFormat();    

    $num = $request->getParameter('num', 10);

    if ($terms = $request->getParameter('q'))
    {
      $entities = EntityTable::getSphinxPager($terms, 1, $num)->execute();
      $entity_ids = array_map(function($e) { return $e["id"]; }, $entities);
      $this->entities = EntityTable::getEntitiesForMap($entity_ids);
    }
    else
    {
      $this->entities = array();
    }

    return $this->renderText(json_encode($this->entities));
  }
}