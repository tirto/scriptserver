<?php
header("content-type: text/html");
require 'includes/common.inc';
require 'includes/db.inc';
require 'includes/aaa_tag_partnermap.inc';
require 'log4php/Logger.php';

# loads partner mapping data from analysts into db
Logger::configure('log4php/dbetl.properties');
$logger = Logger::getLogger("display_partnermap");
$db = new aaa_tag_partnermap($logger);
$db->display_partnermap('travelocity');

?>
