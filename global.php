<?php
error_reporting(E_ERROR);
include_once("workflows.php");
$w = new Workflows();

define("URL_API_T411", "http://api.t411.li/");
define("URL_T411", "http://t411.li/");
define("CYPER_KEY", "acca45ed2534ebbd3ff7398a758eeff3");

$pseudo = $w->get("t.name", "settings.plist");
$password = $w->get("t.pass", "settings.plist");
$password = openssl_decrypt($password, "aes128", CYPER_KEY);

/**
 * @param integer $size
 * @return string
 */
function size_formatted($size) {
  $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
  $power = $size > 0 ? floor(log($size, 1024)) : 0;
  return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

/**
 * @param mixed $errorCode
 * @param Workflows $w
 * @return string
 */
function handleError($errorCode, $w) {
  $err = "Une erreur est survenue ! Code : " . $errorCode;

  switch($errorCode) {
    case "token":
      $err = "Pseudo ou mot de passe incorrect !";
      break;
    case "empty":
      $err = "Plus de résultats :(";
      break;
  }

  $w->result("", "", $err, "", 'icon.png');

  echo $w->toxml();
  exit;
}

/**
 * @param Workflows $w
 */
function showCategory($w) {

  $json = file_get_contents('category.json');
  $categories = json_decode($json);

  foreach ($categories as $masterCategory) {
    foreach ($masterCategory->cats as $cat) {
      $w->result("", "", "#" . $cat->id . " = " . $masterCategory->name . "->" . $cat->name, "", 'icon.png', 'no', "#" . $cat->id . " ");
    }
  }

  echo $w->toxml();
  exit;
}

/**
 * @param string $pseudo
 * @param string $password
 * @param Workflows $w
 * @return string
 */
function regenToken($pseudo, $password, $w) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => URL_API_T411 . "auth",
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => array(
      "username" => $pseudo,
      "password" => $password
    )
  ));

  $resp = curl_exec($curl);
  curl_close($curl);

  $token = json_decode($resp)->token;

  return $token;
}

/**
 * @param string $query
 * @param string $token
 * @return mixed
 */
function searchT411($query, $token) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => URL_API_T411 . "torrents/search/" . urlencode($query) . "?offset=0&limit=1000",
    CURLOPT_HTTPHEADER => array(
      "Content-type: application/xml",
      "Authorization: " . $token,
    )
  ));

  $resp = curl_exec($curl);
  curl_close($curl);

  return json_decode($resp);
}

function searchT411Top($token, $type) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => URL_API_T411 . "torrents/top/" . $type,
    CURLOPT_HTTPHEADER => array(
      "Content-type: application/xml",
      "Authorization: " . $token,
    )
  ));

  $resp = curl_exec($curl);
  curl_close($curl);

  return json_decode($resp);
}

/**
 * @param string $query
 * @param string $token
 */
function downloadTorrentTmp($query, $token) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => URL_API_T411 . "torrents/download/" . $query,
    CURLOPT_HTTPHEADER => array(
      "Content-type: application/xml",
      "Authorization: ".$token,
    )
  ));

  $resp = curl_exec($curl);
  curl_close($curl);

  file_put_contents("/tmp/temp.torrent", $resp);
}

/**
 * @param string $token
 * @param string $query
 * @return string
 */
function getUri($token, $query) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => URL_API_T411 . "torrents/details/" . $query,
    CURLOPT_HTTPHEADER => array(
      "Content-type: application/xml",
      "Authorization: " . $token,
    )
  ));
  $resp = curl_exec($curl);
  curl_close($curl);

  return json_decode($resp)->rewritename;
}

/**
 * @param integer $idCategory
 * @return string
 */
function getCategoryName($idCategory) {
  $json = file_get_contents('category.json');
  $categories = json_decode($json);

  foreach ($categories as $masterCategory) {
    if($masterCategory->id == $idCategory) {
      return $masterCategory->name;
    } else if(isset($masterCategory->cats)) {
      foreach ($masterCategory->cats as $cat) {
        if($cat->id == $idCategory) {
          return $cat->name;
        }
      }
    }
  }

  return $idCategory;
}

/**
 * @param string $str
 * @return string
 */
function formatQuery($str) {
  return strtolower(trim($str));
}

/**
 * @param string $name
 * @param Workflows $w
 * @return string
 */
function extractIdCategory($name, $w) {
  $catId = formatQuery(str_replace("#", "", explode(" ", $name)[0]));

  if(!is_numeric($catId)) {
    showCategory($w);
  }

  return $catId;
}

/**
 * @param $query
 * @param $catId
 * @param $token
 * @return mixed
 */
function searchT411Cat($query, $catId, $token) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => URL_API_T411 . "torrents/search/" . urlencode($query) . "?offset=0&limit=1000&cid=" . $catId,
    CURLOPT_HTTPHEADER => array(
      "Content-type: application/xml",
      "Authorization: " . $token,
    )
  ));

  $resp = curl_exec($curl);
  curl_close($curl);

  return json_decode($resp);
}
