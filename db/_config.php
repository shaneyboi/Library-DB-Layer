<?php
define('DB_HOST', 'localhost');
define('DB_U', 'internaluser');
define('DB_P', 'iota-chi');
define('DB_DB', 'internalcount');

define('VG_DB', 'VGER');
define('VG_U', 'alluhidb');
define('VG_P', 'alluhidb01');

require_once 'local.php';
require_once 'hawn.php';
require_once 'voyager.php';
require_once 'callnumber.php';
require_once 'study-room.php';

$mysql = new Local();
$vg = new Voyager();
?>