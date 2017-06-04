<?php
//TODO https://github.com/thegreenhouseio/php-api-seed/issues/6
//error_reporting(E_ALL | E_STRICT);
//
//require_once "src/services/AuthenticationService.php";
//require_once "src/services/ConfigService.php";
//
//use services as service;
//
//class AuthenticationServiceTest extends PHPUnit_Framework_TestCase{
//  private static $CONFIG = array();
//  private static $TEST_CONFIG = array();
//
//  public function setup() {
//    //determine local vs development config path
//    $configPath = getcwd() === "/vagrant" ? "./ini/config-local.ini" : "/var/www/config-env.ini";
//
//    self::$CONFIG = service\ConfigService::getConfigFromIni($configPath);
//    self::$TEST_CONFIG = array(
//      "session.domain" => self::$CONFIG["session.domain"],
//      "db.host" => self::$CONFIG["db.host"],
//      "db.name" => self::$CONFIG["db.name"],
//      "db.user" => self::$CONFIG["db.user"],
//      "db.password" => self::$CONFIG["db.password"],
//      "key.jwtSecret" => self::$CONFIG["key.jwtSecret"]
//    );
//  }
//
//  public function tearDown(){
//    self::$CONFIG = array();
//    self::$TEST_CONFIG = array();
//  }
//
//  public function testLoginSuccess(){
//    $authService = new service\AuthenticationService(self::$CONFIG);
//    $authStatus = $authService->login(self::$CONFIG["db.user"], self::$CONFIG["db.password"]);
//
//    $this->assertArrayHasKey("success", $authStatus);
//    $this->assertArrayHasKey("message", $authStatus);
//    $this->assertArrayHasKey("data", $authStatus);
//    $this->assertArrayHasKey("jwt", $authStatus["data"]);
//
//    $this->assertEquals($authStatus["success"], true);
//    $this->assertEquals($authStatus["message"], "Login Success");
//  }
//
//  public function testLoginFailureInvalidCredentials(){
//    $authService = new service\AuthenticationService(self::$CONFIG);
//    $authStatus = $authService->login(self::$CONFIG["db.user"], "&&&&");
//
//    $this->assertArrayHasKey("success", $authStatus);
//    $this->assertArrayHasKey("message", $authStatus);
//
//    $this->assertEquals($authStatus["success"], false);
//    $this->assertEquals($authStatus["message"], "Invalid Credentials");
//  }
//
//  public function testLoginFailureNoCredentials(){
//    $authService = new service\AuthenticationService(self::$CONFIG);
//    $authStatus = $authService->login();
//
//    $this->assertArrayHasKey("success", $authStatus);
//    $this->assertArrayHasKey("message", $authStatus);
//
//    $this->assertEquals($authStatus["success"], false);
//    $this->assertEquals($authStatus["message"], "Missing Credentials");
//  }
//
//  public function testLoginFailureNoUsernameCredential(){
//    $authService = new service\AuthenticationService(self::$CONFIG);
//    $authStatus = $authService->login(null, self::$CONFIG["db.password"]);
//
//    $this->assertArrayHasKey("success", $authStatus);
//    $this->assertArrayHasKey("message", $authStatus);
//
//    $this->assertEquals($authStatus["success"], false);
//    $this->assertEquals($authStatus["message"], "Missing Credentials");
//  }
//
//  public function testLoginFailureNoPasswordCredential(){
//    $authService = new service\AuthenticationService(self::$CONFIG);
//    $authStatus = $authService->login(self::$CONFIG["db.user"]);
//
//    $this->assertArrayHasKey("success", $authStatus);
//    $this->assertArrayHasKey("message", $authStatus);
//
//    $this->assertEquals($authStatus["success"], false);
//    $this->assertEquals($authStatus["message"], "Missing Credentials");
//  }
//
//  public function testValidateLoginSuccess(){
//    $authService = new service\AuthenticationService(self::$CONFIG);
//    $authStatus = $authService->login(self::$CONFIG["db.user"], self::$CONFIG["db.password"]);
//    $token = $authStatus["data"]["jwt"];
//
//    //make sure we have reached JWT used time clearance
//    //sleep(11);
//
//    //$loginStatus = $authService->validateLogin($token);
//
//    //TODO fix this!!!
//    //$this->assertEquals('VALID', $loginStatus);
//  }
//
//  public function testValidateLoginFailureInvalidTokenParam(){
//    $authService = new service\AuthenticationService(self::$CONFIG);
//
//    //$this->assertFalse($authService->validateLogin());
//  }
//
//  public function testValidateLoginFailureTokenNotBeforeTime(){
//    $authService = new service\AuthenticationService(self::$CONFIG);
//    $authStatus = $authService->login(self::$CONFIG["db.user"], self::$CONFIG["db.password"]);
//    $token = $authStatus["data"]["jwt"];
//
//    $authStatus = $authService->validateLogin($token);
//
//    //TODO should be a specific status type
//    //TODO fix this!!!
//    //echo 'testValidateLoginFailureTokenNotBeforeTime => ';
//    //var_dump($authStatus);
//    //$this->assertEquals('UNKNOWN', $authStatus);
//  }
//
//  public function testValidateLoginTokenExpired(){
//    $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NTAwMjY2MDEsImp0aSI6ImJyaWhnXC9PQWc3TlhSMVgraXNZZFhBOVViZGJkV3lFV2dEUHpBVFRiRkJ3PSIsImlzcyI6ImFuYWxvZ3N0dWRpb3MudGhlZ3JlZW5ob3VzZS5pbyIsIm5iZiI6MTQ1MDAyNjYxMSwiZXhwIjoxNDUwMDI3ODExLCJkYXRhIjp7InVzZXJJZCI6IjEiLCJ1c2VyTmFtZSI6ImFzdGVzdGVyIn19.q5QC_MBR5OvctYfDM2pyHHHsEVlqd84uFa2qg1Za3riq18jeO2K9RnI8iCVjLfg89J-mm9YPArcmMmjsWU32Lw";
//    $authService = new service\AuthenticationService(self::$CONFIG);
//
//    //TODO fix this!!!
//    $authStatus = $authService->validateLogin($token);
//    //echo 'testValidateLoginTokenExpired => ';
//    //var_dump($authStatus);
//    //$this->assertEquals('EXPIRED', $authStatus);
//  }
//
//  public function testRefreshLoginSuccess(){
//    $authService = new service\AuthenticationService(self::$CONFIG);
//    $authStatus = $authService->login(self::$CONFIG["db.user"], self::$CONFIG["db.password"]);
//
//    //make sure we have reached JWT used time clearance
//    sleep(11);
//
//    $refreshToken = $authService->refreshLogin($authStatus["data"]["jwt"]);
//
//    $this->assertNotNull($refreshToken);
//  }
//
//  public function testRefreshLoginFailureNotToken(){
//    $authService = new service\AuthenticationService(self::$CONFIG);
//    $refreshToken = $authService->refreshLogin();
//
//    $this->assertNull($refreshToken);
//  }
//
//}