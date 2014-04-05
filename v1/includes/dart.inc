<?php
class dart extends db {
  function __construct($logger) {
    $this->logger = Logger::getLogger('dart');
  }
  function load($filename,$partner,$rsid="aca-ncnu-prod") {
    $this->logger->debug("filename=$filename");
    $this->logger->debug("partner=$partner");
    try {
      if(!self::$dbh) $this->connect();
      $lines = file($filename);
      $lineno = 0;
      $sql = "select partner_id from aaa_tag_partners where partner_name = '$partner'";
      $this->logger->debug("sql=$sql");
      $stmt = self::$dbh->prepare($sql);
      $ret = $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      $partner_id = $result['partner_id'];
      $this->logger->debug("partner_id=$partner_id");

      $sql = "select max(page_id) page_id from aaa_tag_pages where partner_id = $partner_id";
      $this->logger->debug("sql=$sql");
      $stmt = self::$dbh->prepare($sql);
      $ret = $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      $page_id = $result['page_id'];
      $this->logger->debug("page_id=$partner_id");
 

      $var_id = 1;
      $active = 1;
      foreach ($lines as $line) {
        $lineno++;
        $page_id++;
//        if ($lineno == 1) continue; // skip header
        $row = explode("\t", $line); 
        //print_r($row);
        print ("----------------------------------------\n");
        print("lineno=$lineno:$line\n");
        $page_url = trim($row[0]);
        $page_name = trim($row[1]);
        print "page_url=$page_url\n";
        print "page_name=$page_name\n";

        $sql = "INSERT INTO aaa_tag_pages_stg
          (page_id,partner_id,page_url,rsid)
          VALUES 
          ($page_id,$partner_id,'$page_url','$rsid')";
        $this->logger->debug("sql=$sql");
        $stmt = self::$dbh->prepare($sql);
        $ret = $stmt->execute();

        $sql = "INSERT INTO aaa_tag_varmap_stg
          (page_id,var_id,var_setting,active)
          VALUES 
          ($page_id,$var_id,'$page_name',$active)";
        $this->logger->debug("sql=$sql");
        $stmt = self::$dbh->prepare($sql);
        $ret = $stmt->execute();
      }  
    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
      $this->logger->error("insert failed:". $e->getMessage());
    }
    return $ret;
  }

}
?>
