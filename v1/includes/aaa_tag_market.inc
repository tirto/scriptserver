<?php
require_once ('/home/aaametrics/log4php/Logger.php');
class aaa_tag_market extends db {
  function __construct($logger) {
    $this->logger = Logger::getLogger('aaa_tag_market');
  }

  function get_market($zipcode) {
    $this->logger->debug("zipcode=$zipcode");
    $market = 'n/a';

    try {
      if(!self::$dbh) $this->connect();
      $sql = "select canon_market_name from aaa_tag_zip_to_market_vw where zipcode = $zipcode";
      $this->logger->debug("sql=$sql");

      $stmt = self::$dbh->prepare($sql);
      $stmt->execute(); 
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $market = $row['canon_market_name'];
        return $market;
      }
    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
      $this->logger->error("load() ERROR:". $e->getMEssage());
    }
    return $market;
  }
}
?>
