<?php

namespace base;

/**
  *
  * @api tgh-api
  * @package base
  * @class AbstractRestfulDatabase
  *
  * @since 0.1.0
  *
  * @copyright 2017
  *
  */

abstract class AbstractRestfulDatabase {
  protected $db = null;

  function __construct($dbType = "", $dbConfig = array()){
    switch ($dbType) {
      case 'PDO':
        try {
          $this->db = new \PDO($dbConfig["dsn"], $dbConfig["username"], $dbConfig["password"]);
          //uncomment below for debugging
          //$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
          echo $e->getMessage();
        }
        break;
      default:
        //echo 'throw expection';
    }
  }

  abstract protected function select ($tableName = '', $id = null, $filterParams = array());
  abstract protected function insert ($tableName = '', $requiredParams = array(), $data = array(), $optionalParams = array());
  abstract protected function update ($tableName = '', $id = null, $updateParams = array(), $params = array());
  abstract protected function delete ($tableName = '', $id = null);
}