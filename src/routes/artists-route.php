<?php

//required in controller.php

/*****************/
/* Artists Routes */
/*****************/
$slim->get("/api/artists", function() use ($slim, $resource) {
  $response = $resource->getArtists();

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});

$slim->get("/api/artists/:id", function($artistId) use ($slim, $resource) {
  $response = $resource->getArtistById($artistId);

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});

$slim->post("/api/artists", function() use ($slim, $resource, $hasValidLogin, $invalidLoginResponse) {
  $params = json_decode($slim->request->getBody(), true);
  $response = $hasValidLogin ? $resource->createArtist($params) : $invalidLoginResponse;

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});

$slim->put("/api/artists/:id", function($artistId) use ($slim, $resource, $hasValidLogin, $invalidLoginResponse) {
  $params = json_decode($slim->request->getBody(), true);
  $response = $hasValidLogin ? $resource->updateArtist($artistId, $params) : $invalidLoginResponse;

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});

$slim->delete("/api/artists/:id", function($artistId) use ($slim, $resource, $hasValidLogin, $invalidLoginResponse) {
  $response = $hasValidLogin ? $resource->deleteArtist($artistId) : $invalidLoginResponse;

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});