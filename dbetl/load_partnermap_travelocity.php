<?php
require '../includes/common.inc';
require '../includes/db.inc';
require '../includes/upload.inc';
require '../log4php/Logger.php';
# loads partner mapping from analysts into db
Logger::configure('../log4php/dbetl.properties');
$logger = Logger::getLogger("load_partnermap");
$db = new aaa_tag_partnermap($logger);
$db->load("$INPUT_DIR/travelocity.txt");
header("content-type: text/html"); 
echo ("load travelocity done<br/>\n");
?>
