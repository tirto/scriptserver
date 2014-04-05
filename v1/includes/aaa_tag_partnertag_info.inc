<?php
require_once ('/home/aaametrics/log4php/Logger.php');
class aaa_tag_partnertag_info extends db {
  function __construct($logger) {
    $this->logger = Logger::getLogger('aaa_tag_partnertag_info');
  }

  function get_partner_tag($partner) {
    $partner_tag = '';
    $tag_script_srcs = '';
    try {
      if(!self::$dbh) $this->connect();
      $sql = "select * from aaa_tag_partnertag_info_vw where partner_name = '$partner' and active = 1 order by tag_id";
      $this->logger->debug("sql=$sql");
      $stmt = self::$dbh->prepare($sql);
      $stmt->execute(); 
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['tag_type'] == 'iframe' || $row['tag_type'] == 'img') {
          $tag_script = $row['tag_script'];
          $this->logger->debug("tag_script=$tag_script");
          if ($tag_script) {
            $partner_tag .= $tag_script;
          }
        }
        if ($row['tag_type'] == 'script') {
          $tag_script_srcs .= "'" . $row['tag_script'] . "',";
        }
      }
    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
      $this->logger->error("load() ERROR:". $e->getMEssage());
    }
    if ($tag_script_srcs) {
      $tag_script_srcs = rtrim($tag_script_srcs, ',');
      $js_tag = <<<END
<!--
YUI().use(function(Y){
Y.Get.script([$tag_script_srcs], {timeout:5000});
});
-->
END;
      $partner_tag .= $js_tag;
    }
    $this->logger->debug("partner_tag=$partner_tag");
    return $partner_tag;
  }
}
?>
