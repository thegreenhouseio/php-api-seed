<?php

//constants
define("PHAR_PATH", "phar://tgh-api.phar");

//load phar
require_once PHAR_PATH;
require_once PHAR_PATH . "/vendor/autoload.php";

/* get config */
$appConfig = \services\ConfigService::getConfigFromIni('config.ini');
$envConfig = \services\ConfigService::getConfigFromIni($appConfig['env_config_ini_path']);

//runtime configuration
ini_set("display_errors", $envConfig["runtime.displayErrors"]);
date_default_timezone_set("America/New_York");

//setup session handling
session_cache_limiter(false);
session_start();

/* new slim */
$slim = new \Slim\Slim();

/* use slim session middleware */
$slim->add(new \Slim\Middleware\SessionCookie(array(
  "domain" => $envConfig["session.domain"]
)));

/* common response headers */
$slim->response->headers->set("Content-Type", "application/json");

/* instantiate authentication service */
//TODO encapsulate / make DRY? ehttps://github.com/thegreenhouseio/php-api-seed/issues/5
$authService = new \services\AuthenticationService($envConfig);
$authHeader = $slim->request->headers->get('Authorization');
$token = sscanf($authHeader, 'Bearer %s')[0];
$loginStatus = $token ? $authService->validateLogin($token) : "";
$hasValidLogin = $loginStatus === 'VALID' ? true : false;

$invalidLoginResponse = array(
  "status" => 401,
  "data" => array(
    "message" => "User not Authenticated"
  )
);

switch ($loginStatus) {
  case "EXPIRED":
    //TODO use 419
    $invalidLoginResponse["data"]["message"] = "User session expired";
    break;
  case "VALID":
    $newToken = $authService->refreshLogin($token);
    $slim->response->headers->set("Authorization", "Bearer " . $newToken);
    break;
}

/* routing and controlling */
$request = $slim->request;
$path = $request->getResourceUri();
$resources = array("albums", "artists");
$route = '';

switch ($path){
  case strpos($path, "albums") !== FALSE:
    $route = 'albums';
    break;
  case strpos($path, "artists") !== FALSE:
    $route = 'artists';
    break;
  case strpos($path, "contact") !== FALSE:
    $route = 'contact';
    break;
  case strpos($path, "login") !== FALSE:
    $route = 'login';
    break;
}

//build a resource based on route
if(in_array($route, $resources)){

  //TODO get entity to pass to respective router
  $builder = new \resources\RestfulResourceBuilder(array(
    "dsn" => "mysql:host=" . $envConfig['db.host'] . ";dbname=" . $envConfig['db.name'],
    "username" => $envConfig["db.user"],
    "password" => $envConfig["db.password"]
  ), $route);

  $resource = $builder->getResource();
};

//TODO make routing OOP - https://github.com/thegreenhouseio/php-api-seed/issues/4
require_once PHAR_PATH . "/routes/" . $route . "-route.php";

//start slim
$slim->run();