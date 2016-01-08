<?php
include_once('global.php');

$token = regenToken($pseudo, $password, $w);

if (trim($token) == "") {
  handleError('token', $w);
}

$res = searchT411($query, $token);

if(isset($res->code)) {
  handleError($res->code, $w);
}

if(!isset($res->torrents) || count($res->torrents) == 0) {
  handleError("empty", $w);
}

$tab_all = array();

foreach ($res->torrents as $item) {
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

foreach ($tab_all as $item) {
  $w->result($item["id"], $item["id"], $item["name"], size_formatted($item["size"]) . ', ' . $item["seeders"] . ' seeders, ' . $item["leechers"] . ' leechers, in ' . $item["category"], 'icon.png');
}

echo $w->toxml();