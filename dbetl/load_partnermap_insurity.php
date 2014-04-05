<?php 
echo("hi");
require '../includes/common.inc'; 
require '../includes/db.inc';
require '../includes/aaa_tag_partnermap.inc';
require '../log4php/Logger.php';
Logger::configure('../log4php/dbetl.properties');

# loads partner mapping data from analysts into db
$logger = Logger::getLogger("load_partnermap");
$logger->debug("start"); 
$db = new aaa_tag_partnermap($logger); 
$db->load("$INPUT_DIR/insurity.txt");
header("content-type: text/html");
echo ("load insurity completed.<br/>\n"); 
?>
