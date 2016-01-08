<?php

include_once('global.php');

$token = regenToken($pseudo, $password, $w);

downloadTorrentTmp($query, $token);