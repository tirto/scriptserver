<?php
require_once ('/home/aaametrics/log4php/Logger.php');
class aaa_tag_settings extends db {
  function __construct($logger) {
    $this->logger = Logger::getLogger('aaa_tag_settings');
  }

  function load($partner, $page_id, $agencies) {
    $result = array();
    $svars = array(); // standard variables
    $cvars = array(); // custom variables
    try {
      if(!self::$dbh) $this->connect();
//      $sql = "select * from aaa_tag_settings_vw where partner_name = :partner and page_url = :page and agency_name = :agency";
//      $stmt->bindParam(':partner',$partner);
//      $stmt->bindParam(':page',$page);
//      $stmt->bindParam(':agency',$agency);
      $sql = "select * from aaa_tag_settings_vw where partner_name = '$partner' and page_id = $page_id and agency_name = 'omniture' and active = 1";
      $this->logger->debug("sql=$sql");
      $stmt = self::$dbh->prepare($sql);
      $stmt->execute(); 
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); 
      foreach ($rows as $row) {
         if ($row['var_type'] == 'custom') {
           array_push($cvars, $row);
         }
         else {
           array_push($svars, $row);
         }
      }
      $result['svars'] = $svars;
      $result['cvars'] = $cvars;

    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
      $this->logger->error("load() ERROR:". $e->getMEssage());
    }
    $this->logger->debug("result=$result");
    return $result;
  }
}
?>
