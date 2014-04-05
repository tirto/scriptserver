<?php
require_once ('/home/aaametrics/log4php/Logger.php');
class aaa_tag_pagetag_info extends db {
  function __construct($logger) {
    $this->logger = Logger::getLogger('aaa_tag_pagetag_info');
  }

  function get_page_tag($page_id) {
    $this->logger->debug("page_id=$page_id");
    $page_id = strlen($page_id) == 0 ? -1 : $page_id;
    $page_tag = '';
    $tag_script_srcs = '';

    try {
      if(!self::$dbh) $this->connect();
      $sql = "select * from aaa_tag_pagetag_info_vw where page_id = $page_id and active = 1 order by tag_id";
      $this->logger->debug("sql=$sql");

      $stmt = self::$dbh->prepare($sql);
      $stmt->execute(); 
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['tag_type'] == 'iframe' || $row['tag_type'] == 'img') {
          $tag_script = $row['tag_script'];
          $this->logger->debug("tag_script=$tag_script");
          if ($tag_script) {
            $page_tag .= $tag_script;
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
      // remove last comma for IE
      $tag_script_srcs = rtrim($tag_script_srcs, ',');
      $js_tag = <<<END
<!--
YUI().use(function(Y){
  Y.Get.script([$tag_script_srcs], {timeout:5000});
});
-->
END;
    }
    $page_tag .= $js_tag;
    $this->logger->debug("page_tag=$page_tag");
    return $page_tag;
    //return '';
  }
}
?>
