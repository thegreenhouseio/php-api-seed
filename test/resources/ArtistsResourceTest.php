<?php

error_reporting(E_ALL | E_STRICT);

require_once "src/base/AbstractRestfulDatabase.php";
require_once "src/base/AbstractRestfulResource.php";
require_once "src/resources/ArtistsResource.php";
require_once "src/resources/RestfulResourceBuilder.php";
require_once "src/services/ConfigService.php";
require_once "src/services/RestfulDatabaseService.php";

use resources as resource;
use services as service;

/**
 *
 * name: ArtistsResourceTest
 *
 * @author Owen Buckley
 */
class ArtistsResourceTest extends PHPUnit_Framework_TestCase{
  private $artistsResource;
  private static $CONFIG = array();
  private static $DB_CONFIG = array();
  private static $SUCCESS = 200;
  private static $CREATED = 201;
  private static $BAD_REQUEST = 400;
  private static $NOT_FOUND = 404;
  private static $MOCK_ARTIST_MODEL = array();

  public function setup(){
    //determine local vs development config path
    $configPath = getcwd() === "/vagrant" ? "./ini/config-local.ini" : "/var/www/config-env.ini";

    self::$CONFIG = service\ConfigService::getConfigFromIni($configPath);
    self::$DB_CONFIG = array(
      "dsn" => "mysql:host=" . self::$CONFIG["db.host"] . ";dbname=" . self::$CONFIG["db.name"],
      "username" => self::$CONFIG["db.user"],
      "password" => self::$CONFIG["db.password"]
    );
    self::$MOCK_ARTIST_MODEL = array(
      "id" => 6,
      "name" => "Dave Flamand",
      "bio" => "Dave Flamand is a talented singer-songwriter from the Block Island area.  Dave is well known on the island for his fun and energetic open mics, where he plays originals as well as covering great acts like Neil Young, the Beatles, Oasis, Blur, and Radiohead.  Dave is also the front man for the rock band Analog and records for both himself and his band.  Check him out live on Block Island or around Providence, RI.  You can keep up with his schedule by following our events page and following Analog Studios social networking sites",
      "genre" => "Rock",
      "location" => "Newport, RI",
      "label" => "Analog Studios",
      "contactPhone" => 1239764333,  //phone numbers need to be unique???
      "contactEmail" => "abc@123.com",
      "imageUrl" => "http://d3cpag05e1ba19.cloudfront.net/hosted/images/artists/dave-flamand.jpg",
      "isActive" => 1
    );

    $builder = new resource\RestfulResourceBuilder(self::$DB_CONFIG, "artists");
    $this->artistsResource = $builder->getResource();
  }

  public function tearDown(){
    $this->artistsResource = null;
    self::$CONFIG = array();
    self::$DB_CONFIG = array();
    self::$MOCK_ARTIST_MODEL = array();
  }

  /**********/
  /* CREATE */
  /**********/
  public function testCreateArtistSuccess(){
    $now = time() * 2;
    $newArtist = array(
      "name" => self::$MOCK_ARTIST_MODEL["name"] . ' ' . $now,
      "bio" => self::$MOCK_ARTIST_MODEL["bio"] . ' ' . $now,
      "contactEmail" => $now . self::$MOCK_ARTIST_MODEL["contactEmail"]
    );

    $response = $this->artistsResource->createArtist($newArtist);
    $status = $response["status"];
    $body = $response["data"];

    $this->assertNotEmpty($body["id"]);
    $this->assertNotEmpty($body["url"]);
    $this->assertEquals(self::$CREATED, $status);
    $this->assertEquals("/api/artists/" . $body["id"], $body["url"]);

    $artistReponse = $this->artistsResource->getArtistById($body['id']);
    $artist = $artistReponse["data"][0];

    $this->assertEquals($artist["name"], $newArtist["name"]);
    $this->assertEquals($artist["bio"], $newArtist["bio"]);
    $this->assertEquals($artist["contactEmail"], $newArtist["contactEmail"]);
  }

  public function testCreateFullArtistSuccess(){
    $now = time();
    $newArtist = array(
      "name" => self::$MOCK_ARTIST_MODEL["name"] . ' ' . $now,
      "bio" => self::$MOCK_ARTIST_MODEL["bio"],
      "genre" => self::$MOCK_ARTIST_MODEL["genre"],
      "location" => self::$MOCK_ARTIST_MODEL["location"],
      "contactPhone" => self::$MOCK_ARTIST_MODEL["contactPhone"],
      "contactEmail" => self::$MOCK_ARTIST_MODEL["contactEmail"],
      "imageUrl" => self::$MOCK_ARTIST_MODEL["imageUrl"] . '?t=' . $now
    );

    $response = $this->artistsResource->createArtist($newArtist);
    $status = $response["status"];
    $body = $response["data"];

    $this->assertNotEmpty($body["id"]);
    $this->assertNotEmpty($body["url"]);
    $this->assertEquals(self::$CREATED, $status);
    $this->assertEquals("/api/artists/" . $body["id"], $body["url"]);

    $artistReponse = $this->artistsResource->getArtistById($body['id']);
    $artist = $artistReponse["data"][0];

    $this->assertEquals($artist["name"], $newArtist["name"]);
    $this->assertEquals($artist["bio"], $newArtist["bio"]);
    $this->assertEquals($artist["genre"], $newArtist["genre"]);
    $this->assertEquals($artist["location"], $newArtist["location"]);
    $this->assertEquals($artist["contactPhone"], $newArtist["contactPhone"]);
    $this->assertEquals($artist["contactEmail"], $newArtist["contactEmail"]);
    $this->assertEquals($artist["imageUrl"], $newArtist["imageUrl"]);
  }


  public function testCreateArtistNoNameFailure(){
    $now = time();
    $newArtist = array(
      "bio" => self::$MOCK_ARTIST_MODEL["bio"] . ' ' . $now
    );

    $response = $this->artistsResource->createArtist($newArtist);
    $status = $response["status"];

    $this->assertEquals(self::$BAD_REQUEST, $status);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  Expected name param", $response["message"]);
  }

  public function testCreatePostNoBioFailure(){
    $now = time();
    $newArtist = array(
      "name" => self::$MOCK_ARTIST_MODEL["name"] . ' ' . $now
    );

    //get response
    $response = $this->artistsResource->createArtist($newArtist);
    $status = $response["status"];

    //assert
    $this->assertEquals(self::$BAD_REQUEST, $status);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  Expected bio param", $response["message"]);
  }

  /********/
  /* READ */
  /********/
  public function testGetAllArtistsSuccess(){
    $response = $this->artistsResource->getArtists();
    $status = $response["status"];
    $data = $response["data"];

    $this->assertEquals(self::$SUCCESS, $status);
    $this->assertNotEmpty($data);
    $this->assertGreaterThanOrEqual(1, count($data));

    for ($i = 0, $l = count($data); $i < $l; $i++) {
      $artist = $data[$i];

      $this->assertArrayHasKey("id", $artist);
      $this->assertArrayHasKey("name", $artist);
      $this->assertArrayHasKey("bio", $artist);

      $this->assertNotEmpty("id", $artist);
      $this->assertNotEmpty("name", $artist);
      $this->assertNotEmpty("bio", $artist);
    }
  }

  public function testGetArtistByIdSuccess(){
    $artists = $this->artistsResource->getArtists();
    $id = $artists["data"][count($artists["data"]) - 1]["id"];

    $response = $this->artistsResource->getArtistById($id);
    $status = $response["status"];
    $data = $response["data"];
    $artist = $data[0];

    $this->assertEquals(self::$SUCCESS, $status);
    $this->assertNotEmpty($data);
    $this->assertEquals(1, count($data));

    $this->assertArrayHasKey("id", $artist);
    $this->assertArrayHasKey("name", $artist);
    $this->assertArrayHasKey("bio", $artist);

    $this->assertNotEmpty("id", $artist);
    $this->assertNotEmpty("name", $artist);
    $this->assertNotEmpty("bio", $artist);
  }

  public function testGetArtistBadRequestFailure(){
    $response = $this->artistsResource->getArtistById('abc');
    $status = $response["status"];

    $this->assertEquals(self::$BAD_REQUEST, $status);
  }

  public function testGetArtistNotFoundFailure(){
    $response = $this->artistsResource->getArtistById(99999999999);
    $status = $response["status"];

    $this->assertEquals(self::$NOT_FOUND, $status);
  }

  /**********/
  /* UPDATE */
  /**********/
  public function testUpdateArtistSuccess(){
    $now = time();
    $response = $this->artistsResource->getArtists();
    $id = $response["data"][count($response["data"]) - 1]["id"];
    $updateArtist = array(
      "name" => self::$MOCK_ARTIST_MODEL["name"] . ' ' . $now,
      "bio" => self::$MOCK_ARTIST_MODEL["bio"] . ' ' . $now,
      "location" => self::$MOCK_ARTIST_MODEL["location"]
    );

    $response = $this->artistsResource->updateArtist($id, $updateArtist);
    $status = $response["status"];
    $data = $response["data"];

    $this->assertEquals(self::$SUCCESS, $status);
    $this->assertEquals("/api/artists/" . $data["id"], $data["url"]);

    $artistReponse = $this->artistsResource->getArtistById($data["id"]);
    $status = $artistReponse["status"];
    $data = $artistReponse["data"];
    $artist = $data[0];

    $this->assertEquals(self::$SUCCESS, $status);
    $this->assertNotEmpty($data);
    $this->assertEquals(1, count($data));

    $this->assertArrayHasKey("id", $artist);
    $this->assertArrayHasKey("name", $artist);
    $this->assertArrayHasKey("bio", $artist);

    $this->assertEquals($updateArtist["name"], $artist["name"]);
    $this->assertEquals($updateArtist["bio"], $artist["bio"]);
  }

  public function testUpdateFullArtistSuccess(){
    $now = time() * 3;
    $response = $this->artistsResource->getArtists();
    $id = $response["data"][count($response["data"]) - 1]["id"];
    $updateArtist = array(
      "name" => self::$MOCK_ARTIST_MODEL["name"] .  ' ' . $now,
      "bio" => self::$MOCK_ARTIST_MODEL["bio"] . ' ' . $now,
      "imageUrl" => self::$MOCK_ARTIST_MODEL["imageUrl"],
      "genre" => self::$MOCK_ARTIST_MODEL["genre"],
      "location" => self::$MOCK_ARTIST_MODEL["location"],
      "contactPhone" => self::$MOCK_ARTIST_MODEL["contactPhone"],
      "contactEmail" => self::$MOCK_ARTIST_MODEL["contactEmail"],
      "isActive" => self::$MOCK_ARTIST_MODEL["isActive"]
    );

    $updateResponse = $this->artistsResource->updateArtist($id, $updateArtist);
    $status = $updateResponse["status"];
    $data = $updateResponse["data"];

    $this->assertEquals(self::$SUCCESS, $status);
    $this->assertEquals("/api/artists/" . $id, $data["url"]);

    $artistReponse = $this->artistsResource->getArtistById($id);
    $artist = $artistReponse["data"][0];

    $this->assertArrayHasKey("id", $artist);
    $this->assertArrayHasKey("name", $artist);
    $this->assertArrayHasKey("bio", $artist);
    $this->assertArrayHasKey("location", $artist);
    $this->assertArrayHasKey("genre", $artist);
    $this->assertArrayHasKey("imageUrl", $artist);
    $this->assertArrayHasKey("contactEmail", $artist);
    $this->assertArrayHasKey("contactPhone", $artist);
    $this->assertArrayHasKey("isActive", $artist);

    $this->assertEquals($updateArtist["name"], $artist["name"]);
    $this->assertEquals($updateArtist["bio"], $artist["bio"]);
    $this->assertEquals($updateArtist["location"], $artist["location"]);
    $this->assertEquals($updateArtist["genre"], $artist["genre"]);
    $this->assertEquals($updateArtist["imageUrl"], $artist["imageUrl"]);
    $this->assertEquals($updateArtist["contactEmail"], $artist["contactEmail"]);
    $this->assertEquals($updateArtist["contactPhone"], $artist["contactPhone"]);
    $this->assertEquals($updateArtist["isActive"], $artist["isActive"]);
  }

  public function testUpdateNoArtistIdFailure(){
    $response = $this->artistsResource->updateArtist();

    $this->assertEquals(self::$BAD_REQUEST, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  No id provided", $response["message"]);
  }

  public function testUpdateArtistNoParamsFailure(){
    $response = $this->artistsResource->updateArtist(1);

    $this->assertEquals(self::$BAD_REQUEST, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  No params provided", $response["message"]);
  }

  public function testUpdateArtistNoValidParamsFailure(){
    $response = $this->artistsResource->updateArtist(1, array("foo" => "bar"));

    $this->assertEquals(self::$BAD_REQUEST, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  No valid params provided", $response["message"]);
  }

  public function testUpdateArtistNotFoundFailure(){
    $response = $this->artistsResource->updateArtist(99999999999999, array("name" => "some new name", "bio" => "some bio"));

    $this->assertEquals(self::$NOT_FOUND, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Resource Not Found", $response["message"]);
  }

  /**********/
  /* DELETE */
  /**********/
  public function testDeleteArtistSuccess(){
    $artistsResponse = $this->artistsResource->getArtists();
    $artist = $artistsResponse["data"][count($artistsResponse["data"]) - 1];

    $response = $this->artistsResource->deleteArtist($artist["id"]);

    $this->assertEquals(self::$SUCCESS, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Resource deleted successfully", $response["message"]);
  }

  public function testDeleteNoArtistIdFailure(){
    $response = $this->artistsResource->deleteArtist();

    $this->assertEquals(self::$BAD_REQUEST, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  No valid id provided", $response["message"]);
  }

  public function testDeleteInvalidArtistIdFailure(){
    $response = $this->artistsResource->deleteArtist("abc");

    $this->assertEquals(self::$BAD_REQUEST, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("Bad Request.  No valid id provided", $response["message"]);
  }

  public function testDeleteArtistNotFoundFailure(){
    $response = $this->artistsResource->deleteArtist(9999999999999999);

    $this->assertEquals(self::$NOT_FOUND, $response["status"]);
    $this->assertEquals(0, count($response["data"]));
    $this->assertEquals("No results found", $response["message"]);
  }
}