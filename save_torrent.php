<?php

include_once('global.php');

$token = regenToken($pseudo, $password, $w);

downloadTorrentTmp($query, $token);

$path = shell_exec("echo ~/Downloads/").$query.".torrent";
$path = str_replace(CHR(10),"",$path);
$path = str_replace(CHR(13),"",$path);

rename("/tmp/temp.torrent", $path);