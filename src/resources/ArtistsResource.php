<?php

namespace resources;

use base as base;


/**
 *
 * @api tgh-api
 * @package resources
 * @uses base base
 * @class ArtistsResource
 *
 * @since 0.1.0
 *
 * @copyright 2017
 *
 */
class ArtistsResource extends base\AbstractRestfulResource{
  private $name = "artists";
  private $tableName = "artists";
  private $requiredParams = array("name", "bio");
  private $updateParams = array("name", "bio", "imageUrl", "genre", "location", "label", "contactPhone", "contactEmail", "isActive");
  private $optionalParams = array("imageUrl", "genre", "location", "label", "contactPhone", "contactEmail", "isActive");

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
  public function getArtists(){
    return $this->db->select($this->tableName);
  }

  public function getArtistById($id = null){
    return $this->db->select($this->tableName, $id);
  }

  public function createArtist($params = array()){
    return $this->db->insert($this->tableName, $this->getRequiredCreateParams(), $params, $this->optionalParams);
  }

  public function updateArtist($id = null, $params = array()){
    return $this->db->update($this->tableName, $id, $this->getAllowedUpdateParams(), $params);
  }

  public function deleteArtist($id = null){
    return $this->db->delete($this->tableName, $id);
  }
}
