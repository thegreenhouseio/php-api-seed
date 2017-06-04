<?php

//required in route.php

/******************/
/* Login Routes */
/******************/
$slim->post("/api/login", function() use ($slim, $authService) {
  $body = json_decode($slim->request->getBody(), true);

  $auth = $authService->login($body["username"], $body["password"]);
  $code = $auth["success"] === true ? 200 : 400;

  $slim->response->status($code);
  $slim->response->setBody(json_encode($auth));
});