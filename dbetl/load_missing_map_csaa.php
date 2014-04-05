<?php
require '/home/aaametrics/v1/includes/common.inc';
require '/home/aaametrics/v1/includes/db.inc';
require '/home/aaametrics/v1/includes/upload.inc';
require '/home/aaametrics/log4php/Logger.php';
// loads partner mapping from analysts into db
Logger::configure('/home/aaametrics/conf/dbetl.properties');
$logger = Logger::getLogger("load_pages");
$db = new upload($logger);
$db->load("/home/aaametrics/input/csaa_pages.txt","csaa","aca-ncnu-prod","UA-1870671-1",1);
echo ("load csaa done<br/>\n");
?>
