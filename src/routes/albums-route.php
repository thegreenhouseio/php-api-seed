<?php

//required in controller.php

/*****************/
/* Albums Routes */
/*****************/
$slim->get("/api/albums", function() use ($slim, $resource) {
  $response = $resource->getAlbums(array(
    "artistId" => $slim->request()->get('artistId'))
  );

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});

$slim->get("/api/albums/:id", function($albumId) use ($slim, $resource) {
  $response = $resource->getAlbumById($albumId);

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});

$slim->get("/api/albums/artist/:id", function($artistId) use ($slim, $resource) {
  $response = $resource->getAlbums(array("artistId" => $artistId));

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});

$slim->post("/api/albums", function() use ($slim, $resource, $hasValidLogin, $invalidLoginResponse) {
  $params = json_decode($slim->request->getBody(), true);
  $response = $hasValidLogin ? $resource->createAlbum($params) : $invalidLoginResponse;

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});

$slim->put("/api/albums/:id", function($albumId) use ($slim, $resource, $hasValidLogin, $invalidLoginResponse) {
  $params = json_decode($slim->request->getBody(), true);
  $response = $hasValidLogin ? $resource->updateAlbum($albumId, $params) : $invalidLoginResponse;

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});

$slim->delete("/api/albums/:id", function($artistId) use ($slim, $resource, $hasValidLogin, $invalidLoginResponse) {
  $response = $hasValidLogin ? $resource->deleteAlbum($artistId) : $invalidLoginResponse;

  $slim->response->status($response['status']);
  $slim->response->setBody(json_encode($response["data"]));
});