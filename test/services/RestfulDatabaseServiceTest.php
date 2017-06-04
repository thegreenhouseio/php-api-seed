<?php
error_reporting(E_ALL | E_STRICT);

require_once "src/base/AbstractRestfulDatabase.php";
require_once "src/services/RestfulDatabaseService.php";

use services as service;

class RestfulDatabaseServiceTest extends PHPUnit_Framework_TestCase{
  private static $CONFIG = array();
  private static $DB_CONFIG = array();
  private $db;

  public function setup(){
    //determine local vs development config path
    $configPath = getcwd() === "/vagrant" ? "./ini/config-local.ini" : "/var/www/config-env.ini";

    self::$CONFIG = service\ConfigService::getConfigFromIni($configPath);
    self::$DB_CONFIG = array(
      "dsn" => "mysql:host=" . self::$CONFIG["db.host"] . ";dbname=" . self::$CONFIG["db.name"],
      "username" => self::$CONFIG["db.user"],
      "password" => self::$CONFIG["db.password"]
    );
    $this->db = new service\RestfulDatabaseService(self::$DB_CONFIG);
  }

  public function tearDown(){
    $this->db = null;
    self::$CONFIG = array();
    self::$DB_CONFIG = array();
  }

  public function testInstanceOf(){
    $this->assertTrue($this->db instanceof service\RestfulDatabaseService);
  }

  public function testInstanceOfParent(){
    $this->assertTrue($this->db instanceof service\RestfulDatabaseService);
    $this->assertTrue(is_subclass_of($this->db, base\AbstractRestfulDatabase::class, false));
  }
}