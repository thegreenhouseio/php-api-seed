<?php

namespace services;

use base as base;

 /**
  *
  * @api tgh-api
  * @package services
  * @uses base base
  * @class RestfulDatabaseService
  *
  * @since 0.1.0
  *
  * @copyright 2017
  *
  */

class RestfulDatabaseService extends base\AbstractRestfulDatabase{
  private static $PATTERN = array(
    "ID" => "/^[0-9]+$/"
  );

  private static $STATUS_CODE = array(
    "BAD_REQUEST" => 400,
    "CREATED" => 201,
    "ERROR" => 500,
    "NOT_FOUND" => 404,
    "NOT_MODIFIED" => 304,
    "SUCCESS" => 200
  );
  private static $STATUS_MESSAGE = array(
    200 => "Success",
    201 => "Created",
    304 => "Not Modified",
    400 => "Bad Request",
    404 => "Not Found",
    500 => "Internal Service Error"
  );

  function __construct($dbConfig = array(), $restfulConfig = array()) {
    parent::__construct('PDO', $dbConfig);
    //tableName
    //requiredParams
    //updateParams
  }

  private function generateResponse ($code = null, $result = array(), $msg = '') {
    $normalizedCode = $code ? $code : 500;
    $normalizedMessage = $msg ? $msg : self::$STATUS_MESSAGE[$normalizedCode];

    return array(
      "status" => $normalizedCode,
      "message" => $normalizedMessage,
      "data" => $result
    );
  }

  public function select($tableName = "", $id = null, $filterParams = array()) {
    $db = $this->db;
    $validId = preg_match(self::$PATTERN["ID"], $id) === 1 ? TRUE : FALSE;
    $validTableName = $tableName !== '' ? TRUE : FALSE;
    $sql = "SELECT * FROM " . $tableName;
    $code = null;

    if($validTableName && $validId){
      $sql .=  " WHERE id=:id";
      $stmt = $db->prepare($sql);
      $stmt->bindValue(":id", $id, $db::PARAM_INT);
    }else{
      $filterSql = null;

      //TODO is this a security vulnerability?
      foreach($filterParams as $key => $value){
        if($value){
          $filterSql = $key . '=:' . $key;
        }
      }

      if($filterSql){
        $sql .= " WHERE " . $filterSql;
      }

      $stmt = $db->prepare($sql);

      if($filterSql){
        foreach($filterParams as $key => $value){
          $type = strpos(strtolower($value), 'id') ? $db::PARAM_INT : $db::PARAM_STR;
          $stmt->bindValue(":" . $key, $value, $type);
        }
      }
    }

    $stmt->execute();
    $result = $stmt->fetchAll($db::FETCH_ASSOC);

    //check by $validId first
    if($id){
      if(!$validId){
        $code = self::$STATUS_CODE["BAD_REQUEST"];
        $result = array();
      }else if($validId && !$result) {
        $code = self::$STATUS_CODE["NOT_FOUND"];
      }else if($validId && $result){
        $code = self::$STATUS_CODE["SUCCESS"];
      }
    }else if(count($result) === 0){
      //no results, so still a "success", but need to set an empty array
      $code = self::$STATUS_CODE["SUCCESS"];
      $result = array();
    }else if($result){
      $code = self::$STATUS_CODE["SUCCESS"];
    }

    return $this->generateResponse($code, $result);
  }

  public function insert($tableName = "", $requiredParams = array(), $data = array(), $optionalParams = array()) {
    $db = $this->db;
    $queryParams = array();
    $query = "INSERT INTO " . $tableName . " ";
    $keys = "(";
    $values = "(";
    $result = array();
    $validParamsNeeded = count($requiredParams);
    $invalidParamError = "";
    $code = null;

    for($i = 0, $l = $validParamsNeeded; $i < $l; $i++){
      $key = $requiredParams[$i];

      if(!isset($data[$key])){
        $invalidParamError .= self::$STATUS_MESSAGE[400] . ".  Expected " . $key . " param";
        break;
      }else{
        $keys .= $key . ",";
        $values .= ":" . $key . ", ";
        $queryParams[":" . $key] = $data[$key];
      }
    };

    //support create request where optional params might be passed
    if(count($optionalParams) > 0){
      for($j = 0, $k = count($optionalParams); $j < $k; $j++){
        $optionalKey = $optionalParams[$j];

        if(isset($data[$optionalKey])){
          $keys .= $optionalKey . ",";
          $values .= ":" . $optionalKey . ", ";
          $queryParams[":" . $optionalKey] = $data[$optionalKey];
          $validParamsNeeded++;
        }
      }
    }

    if($validParamsNeeded === count($queryParams) && $invalidParamError === ""){
      $query = rtrim($query, ", ");
      $keys = rtrim($keys, ", ");
      $values = trim($values, ", ");
      $query .= ($keys . ") VALUES " . $values . ") ");
      $stmt = $db->prepare($query);

      foreach($queryParams as $key => $value){
        $normalKeyLower = strtolower(str_replace(':', '', $key));
        $isIntType = strpos($normalKeyLower, 'id') || strpos($normalKeyLower, 'phone') || strpos($normalKeyLower, 'time');
        $type =  $isIntType ? $db::PARAM_INT : $db::PARAM_STR;
        $stmt->bindValue($key, $queryParams[$key], $type);
      }

      $stmt->execute();

      if($stmt->rowCount() === 1){
        $code = self::$STATUS_CODE["CREATED"];
        $result = array(
          "url" => "/api/" . $tableName . "/" . $db->lastInsertId(),
          "id" => $db->lastInsertId(),
          "createdTime" => time()
        );
      }else{
        $code = self::$STATUS_CODE["ERROR"];
        $invalidParamError = "Unknown Database error.";
      }
    }else{
      $code = self::$STATUS_CODE["BAD_REQUEST"];
    }

    return $this->generateResponse($code, $result, $invalidParamError);
  }

  public function update($tableName = "", $id = null, $updateParams = array(), $data = array()) {
    $db = $this->db;
    $invalidParamError = '';
    $result = array();
    $code = null;

    if(preg_match(self::$PATTERN["ID"], $id) && count($data) > 0) {
      $query = "UPDATE " . $tableName . " SET ";
      $queryParams = array();

      foreach ($data as $key => $value) {
        if (in_array($key, $updateParams)) {
          $query .= $key . "=:" . $key . ", ";
          $queryParams[':' . $key] = $value;
        }
      };

      if(count($queryParams) > 0) {
        $query = rtrim($query, ", ");
        $query .= " WHERE id=:id";
        $queryParams[":id"] = $id;

        $stmt = $db->prepare($query);

        foreach($queryParams as $key => $value){
          $normalKeyLower = strtolower(str_replace(':', '', $key));
          $isIntType = strpos($normalKeyLower, 'id') || strpos($normalKeyLower, 'phone') || strpos($normalKeyLower, 'time');
          $type =  $isIntType ? $db::PARAM_INT : $db::PARAM_STR;
          $stmt->bindValue($key, $queryParams[$key], $type);
        }

        $stmt->execute();
        //$stmt->execute($queryParams);

        if ($stmt->rowCount() === 1) {
          $code = self::$STATUS_CODE["SUCCESS"];
          $result = array(
            "url" => "/api/" . $tableName . "/" . $id,
            "id" => $id
          );
        } else if ($stmt->rowCount() === 0) {
          //echo "SELECT * FROM " . $tableName . " WHERE id=:id";
          $stm = $db->prepare("SELECT * FROM " . $tableName . " WHERE id=:id");
          $stm->bindValue(':id', $id, $db::PARAM_INT);
          $stm->execute();

          $found = $stm->fetch($db::FETCH_NUM) > 0;
          $code = $found ? self::$STATUS_CODE["NOT_MODIFIED"] : self::$STATUS_CODE["NOT_FOUND"];
          $invalidParamError = $found ? "Duplicate data. Resource not modified" : "Resource Not Found";
        } else {
          $code = self::$STATUS_CODE["ERROR"];
          $invalidParamError = "Unkown Database Error";
        }
      }else {
        $code = self::$STATUS_CODE["BAD_REQUEST"];
        $invalidParamError = "Bad Request.  No valid params provided";
      }
    }else{
      $code = self::$STATUS_CODE["BAD_REQUEST"];
      $missing = !$id ? "id" : "params";
      $invalidParamError = "Bad Request.  No " . $missing . " provided";
    }

    return $this->generateResponse($code, $result, $invalidParamError);
  }

  public function delete($tableName = "", $id = null) {
    $db = $this->db;
    $result = array();
    $invalidParamError = "";
    $code = null;

    if(preg_match(self::$PATTERN["ID"], $id)) {
      $stmt = $db->prepare("DELETE FROM " . $tableName . " WHERE id=:id");
      $stmt->bindValue(":id", $id, $db::PARAM_INT);
      $stmt->execute();

      if ($stmt->rowCount() === 1) {
        $code = self::$STATUS_CODE["SUCCESS"];
        $invalidParamError = "Resource deleted successfully";
      } else if ($stmt->rowCount() === 0) {
        $code = self::$STATUS_CODE["NOT_FOUND"];
        $invalidParamError = "No results found";
      } else {
        $code = self::$STATUS_CODE["ERROR"];
        $invalidParamError = "Unknown Database Error";
      }
    }else{
      $code = self::$STATUS_CODE["BAD_REQUEST"];
      $invalidParamError = "Bad Request.  No valid id provided";
    }

    return $this->generateResponse($code, $result, $invalidParamError);
  }

}