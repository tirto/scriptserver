<?php
require '/home/aaametrics/v1/includes/common.inc';
require '/home/aaametrics/v1/includes/db.inc';
require '/home/aaametrics/v1/includes/upload.inc';
require '/home/aaametrics/log4php/Logger.php';
# loads partner mapping from analysts into db
Logger::configure('../log4php/dbetl.properties');
$logger = Logger::getLogger("load_pages");
$db = new upload($logger);
$db->load("/home/aaametrics/input/travelocity_pages.txt","travelocity","aca-ncnu-prod","UA-1870671-1",1);
echo ("load travelocity done<br/>\n");
?>
