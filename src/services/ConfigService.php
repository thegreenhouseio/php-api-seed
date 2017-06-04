<?php

namespace services;

/**
 *
 * @api tgh-api
 * @package services
 * @class ConfigService
 *
 * @since 0.1.0
 *
 * @copyright 2017
 *
 */

class ConfigService{

  private static function loadIni($path = ''){
    return parse_ini_file($path, true);
  }

  public static function getConfigFromIni($path = ''){

    if(file_exists($path)){
      return self::loadIni($path);
    }else{
      throw new \InvalidArgumentException('Invalid Path');
    }
  }

}