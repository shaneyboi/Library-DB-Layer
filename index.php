<?php

use UHHiloLibrary\Voyager\Voyager as Voyager;
use UHHiloLibrary\Voyager\Status as Status;
use UHHiloLibrary\Voyager\StudyRoom as StudyRoom;
use UHHiloLibrary\Circulation\InternalCount as InternalCount;
use UHHiloLibrary\Circulation\Information as Information;
use UHHiloLibrary\Circulation\ExitCount as ExitCount;

require_once 'lib/start.php';
define('VG_DB', 'VGER');
define('VG_U', 'alluhidb');
define('VG_P', 'alluhidb01');

$DB = array(
	"Voyager" => new Voyager(),
	"Status" => new Status(),
	"Study Room" => new StudyRoom(),
	"Internal Count" => new InternalCount(),
	"Exit" => new ExitCount(),
	"Information" => new Information()
);

$item = 80000046370;
$item = 80000123456;
$date = '10/01/2016';

echo "<ol>";
foreach ($DB as $key => $value) {
	echo "<li>{$key}: {$value->checkConnection()}</li>";
}
echo "</ol>";

var_dump($DB['Voyager']->get(80000123456));

var_dump($DB['Information']->getInfo($item));

?>
