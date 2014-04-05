<?php
require_once ('/home/aaametrics/log4php/Logger.php');
class aaa_tag_pageinfo extends db {
  function __construct($logger) {
    $this->logger = Logger::getLogger('aaa_tag_pageinfo');
  }

  function get_partner_rsid($page_url, $referrer='', $query_param='', $referrer_query_param='') {
    $partner = '';
    $rsid = '';
    $page_id = '';
    $where = '';
    if ($referrer) {
      $where .= " and referrer like '$referrer%'";
    }
    if ($query_param) {
      $where .= " and query_param like '%$query_param%'";
    }
    if ($referrer_query_param) {
      $where .= " and referrer_query_param like '%$referrer_query_param%'";
    }
      $this->logger->debug("where=$where");
    try {
      if(!self::$dbh) $this->connect();
      $sql = "select page_id, rsid, partner_name from aaa_tag_pageinfo_vw where page_url = '$page_url' $where";
      $this->logger->debug("sql=$sql");
      $stmt = self::$dbh->prepare($sql);
      $stmt->execute(); 
      $row = $stmt->fetch(PDO::FETCH_ASSOC); 
      $partner = $row['partner_name'];
      $rsid = $row['rsid'];
      $page_id = $row['page_id'];

    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
      $this->logger->error("load() ERROR:". $e->getMessage());
    }
    $this->logger->debug("partner=$partner;rsid=$rsid;page_id=$page_id");
    return array($partner,$rsid,$page_id);
  }
}
?>
