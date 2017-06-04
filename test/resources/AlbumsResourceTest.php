<?php

error_reporting(E_ALL | E_STRICT);

require_once "src/base/AbstractRestfulDatabase.php";
require_once "src/base/AbstractRestfulResource.php";
require_once "src/resources/AlbumsResource.php";
require_once "src/resources/RestfulResourceBuilder.php";
require_once "src/services/ConfigService.php";
require_once "src/services/RestfulDatabaseService.php";

use resources as resource;
use services as service;

class AlbumsResourceTest extends PHPUnit_Framework_TestCase{
  private $albumsResource;
  private static $CONFIG = array();
  private static $DB_CONFIG = array();
  private static $SUCCESS = 200;
  private static $CREATED = 201;
  private static $BAD_REQUEST = 400;
  private static $NOT_FOUND = 404;
  private static $MOCK_ALBUM_MODEL = array();

  public function setup(){
    //determine local vs development config path
    //TODO improve this https://github.com/thegreenhouseio/php-api-seed/issues/12
    $configPath = getcwd() === "/vagrant" ? "./ini/config-local.ini" : "/var/www/config-env.ini";

    self::$CONFIG = service\ConfigService::getConfigFromIni($configPath);
    self::$DB_CONFIG = array(
      "dsn" => "mysql:host=" . self::$CONFIG["db.host"] . ";dbname=" . self::$CONFIG["db.name"],
      "username" => self::$CONFIG["db.user"],
      "password" => self::$CONFIG["db.password"]
    );
    self::$MOCK_ALBUM_MODEL = array(
      "title" => "Mock Model - Debut CD Release Party (live)",
      "description" => "Mock Model - The lead singer of Analog, Dave Flamand is from Rhode Island and we are pleased to offer you exclusive downloads of his demo from this site. These songs provided the framework leading up to the creation of Analog, and as such you may recognize most of the songs from When The Media Talks About The Media from these demos. Lost Time was released early 2008 and Spare Time followed shortly thereafter. Lost Time is Dave&#39;s acoustic debut, showcasing his talent as songwriter and versatile musician. The are all of his own original recordings made on Block Island and recorded by himself. Dave not only wrote all the songs, but also played all the instruments himself",
      "year" => 2017,
      "imageUrl" => "http://d3cpag05e1ba19.cloudfront.net/hosted/albums/dave-flamand/lost-time/lost-time.jpg",
      "downloadUrl" => "http://d3cpag05e1ba19.cloudfront.net/hosted/albums/dave-flamand/lost-time/lost-time.zip",
      "artistId" => 1
    );

    $builder = new resource\RestfulResourceBuilder(self::$DB_CONFIG, "albums");
    $this->albumsResource = $builder->getResource();
  }

  public function tearDown(){
    $this->albumsResource = null;
    self::$CONFIG = array();
    self::$DB_CONFIG = array();
    self::$MOCK_ALBUM_MODEL = array();
  }

  /**********/
  /* CREATE */
  /**********/
  public function testCreateAlbumSuccess(){
    $now = time();
    $newAlbum = array(
      "title" => self::$MOCK_ALBUM_MODEL["title"] . ' ' . $now,
      "description" => self::$MOCK_ALBUM_MODEL["description"],
      "artistId" => self::$MOCK_ALBUM_MODEL["artistId"]
    );

    $response = $this->albumsResource->createAlbum($newAlbum);
    $status = $response["status"];
    $body = $response["data"];

    $this->assertNotEmpty($body["id"]);
    $this->assertNotEmpty($body["url"]);
    $this->assertEquals(self::$CREATED, $status);
    $this->assertEquals("/api/albums/" . $body["id"], $body["url"]);

    $artistReponse = $this->albumsResource->getAlbumById($body['id']);
    $artist = $artistReponse["data"][0];

    $this->assertEquals($artist["title"], $newAlbum["title"]);
    $this->assertEquals($artist["description"], $newAlbum["description"]);
  }

  public function testCreateFullAlbumSuccess(){
    $now = time();
    $newAlbum = array(
      "title" => self::$MOCK_ALBUM_MODEL["title"] . ' ' . $now,
      "description" => self::$MOCK_ALBUM_MODEL["description"],
      "year" => self::$MOCK_ALBUM_MODEL["year"] + 1,
      "imageUrl" => self::$MOCK_ALBUM_MODEL["imageUrl"] . "?t=" . $now,
      "downloadUrl" => self::$MOCK_ALBUM_MODEL["downloadUrl"] . "?t=" . $now,
      "artistId" => self::$MOCK_ALBUM_MODEL["artistId"]
    );
    $response = $this->albumsResource->createAlbum($newAlbum);

    $status = $response["status"];
    $body = $response["data"];
    $this->assertNotEmpty($body["id"]);
    $this->assertNotEmpty($body["url"]);
    $this->assertEquals(self::$CREATED, $status);
    $this->assertEquals("/api/albums/" . $body["id"], $body["url"]);

    $albumsReponse = $this->albumsResource->getAlbumById($body['id']);
    $album = $albumsReponse["data"][0];

    $this->assertEquals($album["title"], $newAlbum["title"]);
    $this->assertEquals($album["description"], $newAlbum["description"]);
    $this->assertEquals($album["year"], $newAlbum["year"]);
    $this->assertEquals($album["imageUrl"], $newAlbum["imageUrl"]);
    $this->assertEquals($album["downloadUrl"], $newAlbum["downloadUrl"]);
    $this->assertEquals($album["artistId"], $newAlbum["artistId"]);
  }

  public function testCreateAlbumNoTitleFailure(){
    $newAlbum = array(
      "description" => self::$MOCK_ALBUM_MODEL["description"]
    );

    $response = $this->albumsResource->createAlbum($newAlbum);
    $status = $response["status"];

    $this->assertEquals(self::$BAD_REQUEST, $status);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  Expected title param", $response["message"]);
  }

  public function testCreateAlbumNoDescriptionFailure(){
    $newAlbum = array(
      "title" => self::$MOCK_ALBUM_MODEL["title"]
    );

    //get response
    $response = $this->albumsResource->createAlbum($newAlbum);
    $status = $response["status"];

    //assert
    $this->assertEquals(self::$BAD_REQUEST, $status);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  Expected description param", $response["message"]);
  }

  public function testCreateAlbumNoArtistIdFailure(){
    $newAlbum = array(
      "title" => self::$MOCK_ALBUM_MODEL["title"],
      "description" => self::$MOCK_ALBUM_MODEL["description"]
    );

    //get response
    $response = $this->albumsResource->createAlbum($newAlbum);
    $status = $response["status"];

    //assert
    $this->assertEquals(self::$BAD_REQUEST, $status);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  Expected artistId param", $response["message"]);
  }


  /********/
  /* READ */
  /********/
  public function testGetAllAlbumsSuccess(){
    $response = $this->albumsResource->getAlbums();
    $status = $response["status"];
    $data = $response["data"];

    $this->assertEquals(self::$SUCCESS, $status);
    $this->assertNotEmpty($data);
    $this->assertGreaterThanOrEqual(1, count($data));

    for ($i = 0, $l = count($data); $i < $l; $i++) {
      $album = $data[$i];

      $this->assertArrayHasKey("id", $album);
      $this->assertArrayHasKey("title", $album);
      $this->assertArrayHasKey("description", $album);
      $this->assertArrayHasKey("artistId", $album);

      $this->assertNotEmpty("id", $album);
      $this->assertNotEmpty("title", $album);
      $this->assertNotEmpty("description", $album);
      $this->assertNotEmpty("artistId", $album);
    }
  }

  public function testGetAlbumsByIdSuccess(){
    $albums = $this->albumsResource->getAlbums();
    $id = $albums["data"][count($albums["data"]) - 1]["id"];

    $response = $this->albumsResource->getAlbumById($id);
    $status = $response["status"];
    $data = $response["data"];
    $album = $data[0];

    $this->assertEquals(self::$SUCCESS, $status);
    $this->assertNotEmpty($data);
    $this->assertEquals(1, count($data));

    $this->assertArrayHasKey("id", $album);
    $this->assertArrayHasKey("title", $album);
    $this->assertArrayHasKey("description", $album);

    $this->assertNotEmpty("id", $album);
    $this->assertNotEmpty("title", $album);
    $this->assertNotEmpty("description", $album);
  }

  public function testGetAlbumsByArtistId(){
    //we can use mock artistId since it will be part of the APIs initial production data
    $albumsResponse = $this->albumsResource->getAlbums(array(
      "artistId" => self::$MOCK_ALBUM_MODEL["artistId"]
    ));
    $hasAlbums = false;

    $status = $albumsResponse["status"];
    $albums = $albumsResponse["data"];

    $this->assertEquals(self::$SUCCESS, $status);
    $this->assertNotEmpty($albums);

    for($i = 0, $l = count($albums); $i < $l; $i++){
      $album = $albums[$i];

      if($album["artistId"] == self::$MOCK_ALBUM_MODEL["artistId"]){
        $this->assertArrayHasKey("id", $album);
        $this->assertArrayHasKey("title", $album);
        $this->assertArrayHasKey("description", $album);
        $this->assertArrayHasKey("year", $album);
        $this->assertArrayHasKey("imageUrl", $album);
        $this->assertArrayHasKey("downloadUrl", $album);
        $this->assertArrayHasKey("artistId", $album);

        $this->assertNotEmpty("id", $album);
        $this->assertNotEmpty("title", $album);
        $this->assertNotEmpty("description", $album);
        $this->assertNotEmpty("year", $album);
        $this->assertNotEmpty("imageUrl", $album);
        $this->assertNotEmpty("downloadUrl", $album);
        $this->assertNotEmpty("artistId", $album);

        $this->assertEquals(self::$MOCK_ALBUM_MODEL["artistId"], $album["artistId"]);

        $hasAlbums = true;
      }
    }

    $this->assertTrue($hasAlbums);
  }

  public function testGetAlbumBadRequestFailure(){
    $response = $this->albumsResource->getAlbumById('abc');
    $status = $response["status"];

    $this->assertEquals(self::$BAD_REQUEST, $status);
  }

  public function testGetArtistNotFoundFailure(){
    $response = $this->albumsResource->getAlbumById(99999999999);
    $status = $response["status"];

    $this->assertEquals(self::$NOT_FOUND, $status);
  }

  /**********/
  /* UPDATE */
  /**********/
  public function testUpdateAlbumSuccess(){
    $now = time() * 2;
    $response = $this->albumsResource->getAlbums();
    $id = $response["data"][count($response["data"]) - 1]["id"];

    $albumsReponse = $this->albumsResource->updateAlbum($id, array(
      "title" => self::$MOCK_ALBUM_MODEL["title"] . $now,
      "description" => self::$MOCK_ALBUM_MODEL["description"] . $now,
    ));

    $status = $albumsReponse["status"];
    $data = $albumsReponse["data"];

    $this->assertEquals(self::$SUCCESS, $status);
    $this->assertEquals("/api/albums/" . $data["id"], $data["url"]);

    $singleAlbumReponse = $this->albumsResource->getAlbumById($data['id']);
    $singlAlbum = $singleAlbumReponse["data"][0];

    $this->assertEquals($singlAlbum["title"], self::$MOCK_ALBUM_MODEL["title"] . $now);
    $this->assertEquals($singlAlbum["description"], self::$MOCK_ALBUM_MODEL["description"] . $now);
  }

  public function testUpdateFullAlbumSuccess(){
    $now = time() * 3;
    $response = $this->albumsResource->getAlbums();
    $id = $response["data"][count($response["data"]) - 1]["id"];

    $albumsReponse = $this->albumsResource->updateAlbum($id, array(
      "title" => self::$MOCK_ALBUM_MODEL["title"]  . ' ' . $now,
      "description" => self::$MOCK_ALBUM_MODEL["description"]  . ' ' . $now,
      "year" => self::$MOCK_ALBUM_MODEL["year"] + 1,
      "imageUrl" => self::$MOCK_ALBUM_MODEL["imageUrl"] . "?=" . $now,
      "downloadUrl" => self::$MOCK_ALBUM_MODEL["downloadUrl"] . "?=" . $now
    ));

    $status = $albumsReponse["status"];
    $album = $albumsReponse["data"];

    $this->assertEquals(self::$SUCCESS, $status);
    $this->assertEquals("/api/albums/" . $album["id"], $album["url"]);

    $singleAlbumReponse = $this->albumsResource->getAlbumById($album['id']);
    $singlAlbum = $singleAlbumReponse["data"][0];

    $this->assertEquals($singlAlbum["title"], self::$MOCK_ALBUM_MODEL["title"]  . ' ' . $now);
    $this->assertEquals($singlAlbum["description"], self::$MOCK_ALBUM_MODEL["description"] . ' ' . $now);
    $this->assertEquals($singlAlbum["year"], (self::$MOCK_ALBUM_MODEL["year"] + 1));
    $this->assertEquals($singlAlbum["imageUrl"], (self::$MOCK_ALBUM_MODEL["imageUrl"] . "?=" . $now));
    $this->assertEquals($singlAlbum["downloadUrl"], (self::$MOCK_ALBUM_MODEL["downloadUrl"] . "?=" . $now));
  }

  public function testUpdateNoAlbumIdFailure(){
    $response = $this->albumsResource->updateAlbum();

    $this->assertEquals(self::$BAD_REQUEST, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  No id provided", $response["message"]);
  }

  public function testUpdateAlbumNoParamsFailure(){
    $response = $this->albumsResource->updateAlbum(1);

    $this->assertEquals(self::$BAD_REQUEST, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  No params provided", $response["message"]);
  }

  public function testUpdateAlbumNoValidParamsFailure(){
    $response = $this->albumsResource->updateAlbum(1, array("foo" => "bar"));

    $this->assertEquals(self::$BAD_REQUEST, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  No valid params provided", $response["message"]);
  }

  public function testUpdateAlbumNotFoundFailure(){
    $response = $this->albumsResource->updateAlbum(99999999999999, array(
      "title" => self::$MOCK_ALBUM_MODEL["title"],
      "description" => self::$MOCK_ALBUM_MODEL["description"]
    ));

    $this->assertEquals(self::$NOT_FOUND, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Resource Not Found", $response["message"]);
  }

  /**********/
  /* DELETE */
  /**********/
  public function testDeleteAlbumSuccess(){
    $artistsResponse = $this->albumsResource->getAlbums();
    $artist = $artistsResponse["data"][count($artistsResponse["data"]) - 1];

    $response = $this->albumsResource->deleteAlbum($artist["id"]);

    $this->assertEquals(self::$SUCCESS, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Resource deleted successfully", $response["message"]);
  }

  public function testDeleteNoAlbumIdFailure(){
    $response = $this->albumsResource->deleteAlbum();

    $this->assertEquals(self::$BAD_REQUEST, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  No valid id provided", $response["message"]);
  }

  public function testDeleteInvalidAlbumIdFailure(){
    $response = $this->albumsResource->deleteAlbum("abc");

    $this->assertEquals(self::$BAD_REQUEST, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  No valid id provided", $response["message"]);
  }

  public function testDeleteAlbumNotFoundFailure(){
    $response = $this->albumsResource->deleteAlbum(9999999999999999);

    $this->assertEquals(self::$NOT_FOUND, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("No results found", $response["message"]);
  }
}