<?php

include_once('global.php');

$token = regenToken($pseudo, $password, $w);

if (trim($token) == "") {
  handleError('token', $w);
}

$res = null;

if(strpos(formatQuery($query), "@today") === 0) {
  $torrents = searchT411Top($token, "today");
} elseif(strpos(formatQuery($query), "@week") === 0) {
  $torrents = searchT411Top($token, "week");
} elseif(strpos(formatQuery($query), "@month") === 0) {
  $torrents = searchT411Top($token, "month");
} elseif(strpos(formatQuery($query), "@100") === 0) {
  $torrents = searchT411Top($token, "100");
} elseif(strpos(formatQuery($query), "#") === 0) {
  $idCat = extractIdCategory($query, $w);

  $query = explode(" ", trim($query));
  array_shift($query);
  $query = implode(" ", $query);

  $res = searchT411Cat($query, $idCat, $token);

  if(isset($res->code)) {
    handleError($res->code, $w);
  }

  if(!isset($res->torrents) || count($res->torrents) == 0) {
    handleError("empty", $w);
  }

  $torrents = $res->torrents;

} else {
  $res = searchT411($query, $token);

  if(isset($res->code)) {
    handleError($res->code, $w);
  }

  if(!isset($res->torrents) || count($res->torrents) == 0) {
    handleError("empty", $w);
  }

  $torrents = $res->torrents;
}

$tab_all = array();

foreach ($torrents as $item) {
  $tab_all[] = array(
    'id' => $item->id,
    'name' => $item->name,
    'size' => $item->size,
    'seeders' => $item->seeders,
    'leechers' => $item->leechers,
    'category' => $item->category
  );
}

foreach ($tab_all as $key => $row) {
  $seeders[$key] = $row['seeders'];
}

array_multisort($seeders, SORT_DESC, $tab_all);

foreach ($tab_all as $index => $item) {
  if($index > 100) {
    break;
  }

  $catName = getCategoryName($item["category"]);
  $w->result($item["id"], $item["id"], $item["name"], size_formatted($item["size"]) . ', ' . $item["seeders"] . ' seeders, ' . $item["leechers"] . ' leechers, in ' . $catName, 'icon.png');
}

echo $w->toxml();