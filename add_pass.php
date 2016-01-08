<?php
require('global.php');
exec('touch settings.plist');
$w->set('t.pass', openssl_encrypt($query, 'aes128', CYPER_KEY), 'settings.plist');
