<?php
require '../includes/db.inc';
require '../includes/aaa_tag_partnermap.inc';
require '../log4php/Logger.php';
Logger::configure('../log4php/dbetl.properties');
# reset variable mappings
$logger = Logger::getLogger("reset_varmap");
$db = new aaa_tag_partnermap($logger);
$agency_id = 1; // omniture
$db->reset_varmap('insurity', $agency_id);

header("content-type: text/html");
echo ("reset varmap insurity done<br/>\n");
?>
