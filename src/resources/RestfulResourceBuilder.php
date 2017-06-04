<?php

namespace resources;

use services as service;

 /**
  *
  * @api tgh-api
  * @package resources
  * @uses base base
  * @class RestfulResourceBuilder
  *
  * @since 0.1.0
  *
  * @copyright 2017
  *
  */
class RestfulResourceBuilder {
  private $entityType;

  function __construct($dbConfig = array(), $entityType = "") {
    if($dbConfig && count($dbConfig) === 3 && $entityType !== ""){
      $this->entityType = $entityType;
      $this->db = new service\RestfulDatabaseService($dbConfig);
    }else{
      throw new \InvalidArgumentException('Invalid Constructor Params');
    }
  }

  /**
   *
   * @method buildEntity
   *
   * @return RestfulResource
   */
  private function buildResource(){
    $entity = NULL;

    switch (strtolower($this->entityType)){
      case "albums":
        $entity = new AlbumsResource($this->db);
        break;
      case "artists":
        $entity = new ArtistsResource($this->db);
        break;
      default:
        //throw exception
    }

    return $entity;
  }

  /**
   *
   * @method getResource
   *
   * @return RestfulResource;
   */
  public function getResource () {
    return $this->buildResource();
  }
}