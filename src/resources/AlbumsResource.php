<?php

namespace resources;

use base as base;


/**
 *
 * @api tgh-api
 * @package resources
 * @uses base base
 * @class AlbumsResource
 *
 * @since 0.1.0
 *
 * @copyright 2017
 *
 */
class AlbumsResource extends base\AbstractRestfulResource{
  private $name = "albums";
  private $tableName = "albums";
  private $requiredParams = array("title", "description", "artistId");
  private $updateParams = array("title", "description", "imageUrl", "downloadUrl", "year");
  private $optionalParams = array("year", "imageUrl", "downloadUrl");

  //abstract getters
  public function getName(){
    return $this->name;
  }

  public function getTableName(){
    return $this->tableName;
  }

  public function getRequiredCreateParams(){
    return $this->requiredParams;
  }

  public function getAllowedUpdateParams(){
    return $this->updateParams;
  }

  //resource level methods
  public function getAlbums($filterParams = array()){
    return $this->db->select($this->tableName, null, $filterParams);
  }

  public function getAlbumById($id = null){
    return $this->db->select($this->tableName, $id);
  }

  public function createAlbum($params = array()){
    return $this->db->insert($this->tableName, $this->getRequiredCreateParams(), $params, $this->optionalParams);
  }

  public function updateAlbum($id = null, $params = array()){
    return $this->db->update($this->tableName, $id, $this->getAllowedUpdateParams(), $params);
  }

  public function deleteAlbum($id = null){
    return $this->db->delete($this->tableName, $id);
  }
}