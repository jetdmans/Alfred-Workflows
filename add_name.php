<?php
require('workflows.php');
$w = new Workflows();

exec('touch settings.plist');

$w->set('t.name', $query, 'settings.plist');

echo $query;
