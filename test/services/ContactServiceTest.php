<?php

error_reporting(E_ALL | E_STRICT);

require_once "src/services/ConfigService.php";
require_once "src/services/ContactService.php";

use services as service;

class ContactServiceTest extends PHPUnit_Framework_TestCase{
  private static $CONFIG = array();
  private static $MAILER_CONFIG = array();
  private static $fromEmail = "website@analogstudios.net";
  private static $validEmail = "abc123@analogstudios.net";  //change to a valid address for testing verification
  private static $invalidEmail = "blahblah.net";
  private static $subject = "A message from the website";
  private static $message = "I really like your website!";
  private $contactService;

  public function setup(){
    //determine local vs development config path
    $configPath = getcwd() === "/vagrant" ? "./ini/config-local.ini" : "/var/www/config-env.ini";

    self::$CONFIG = service\ConfigService::getConfigFromIni($configPath);
    self::$MAILER_CONFIG = array(
      "host" => self::$CONFIG["mail.host"],
      "username" => self::$CONFIG["mail.username"],
      "password" => self::$CONFIG["mail.password"],
      "port" => self::$CONFIG["mail.port"]
    );

    $this->contactService = new service\ContactService(self::$MAILER_CONFIG);
  }

  public function tearDown(){
    $this->contactService = null;
    self::$CONFIG = array();
    self::$MAILER_CONFIG = array();
  }

  /********/
  /* Send Email  */
  /********/
  //  public function testSendEmailSuccess(){
  //    $response = $this->contactService->sendEmail(self::$CONFIG["mail.to"], self::$fromEmail, self::$subject, self::$message);
  //
  //    $this->assertEquals($response["status"], 200);
  //    $this->assertEquals($response["message"], "Message has been sent");
  //  }

  public function testSendEmailInvalidToEmailFailure(){
    $response = $this->contactService->sendEmail(self::$invalidEmail, self::$fromEmail, self::$subject, self::$message);

    $this->assertEquals($response["status"], 400);
    $this->assertEquals($response["message"], "Invalid to email address");
  }

  public function testSendEmailInvalidFromEmailFailure(){
    $response = $this->contactService->sendEmail(self::$validEmail, self::$invalidEmail, self::$subject, self::$message);

    $this->assertEquals($response["status"], 400);
    $this->assertEquals($response["message"], "Invalid from email address");
  }

  public function testSendEmailNoSubjectFailure(){
    $response = $this->contactService->sendEmail(self::$validEmail, self::$fromEmail, "", self::$message);

    $this->assertEquals($response["status"], 400);
    $this->assertEquals($response["message"], "No subject");
  }

  public function testSendEmailNoMessageFailure(){
    $response = $this->contactService->sendEmail(self::$validEmail, self::$fromEmail, self::$subject, "");

    $this->assertEquals($response["status"], 400);
    $this->assertEquals($response["message"], "No message");
  }

}