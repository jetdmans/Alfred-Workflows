<?php

include_once('global.php');

$token = regenToken($pseudo, $password, $w);

$uri = getUri($token, $query);

exec("open " . URL_T411 . "torrents/" . $uri);