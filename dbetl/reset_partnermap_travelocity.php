<?php
require '../includes/db.inc';
require '../includes/aaa_tag_partnermap.inc';
require '../log4php/Logger.php';
# reset variable mappings
Logger::configure('../log4php/dbetl.properties');
$logger = Logger::getLogger("reset_varmap");
$db = new aaa_tag_partnermap($logger);
$agency_id = 1; // omniture
$db->reset_varmap('travelocity', $agency_id);
header("content-type: text/html");
echo ("reset varmap travelocity done<br/>\n");

?>
