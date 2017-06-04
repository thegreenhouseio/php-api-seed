<?php

namespace base;

use services as service;

 /**
  *
  * @api tgh-api
  * @package base
  * @uses base base
  * @class AbstractRestfulResource
  *
  * @since 0.1.0
  *
  * @copyright 2017
  *
  */

abstract class AbstractRestfulResource{
  protected $db;

  function __construct(service\RestfulDatabaseService $db) {
    $this->db = $db;
  }

  abstract protected function getName ();
  abstract protected function getTableName ();
  abstract protected function getRequiredCreateParams ();
  abstract protected function getAllowedUpdateParams ();
}