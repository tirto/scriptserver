<?php
class aaa_tag_partnermap extends db {
  function __construct($logger) {
    $this->logger = Logger::getLogger('aaa_tag_partnermap');
  }
  function load($filename) {
    $this->logger->debug("filename=$filename");
    try {
      if(!self::$dbh) $this->connect();
      $lines = file($filename);
      $lineno = 0;
      foreach ($lines as $line) {
        $lineno++;
        if ($lineno == 1) continue; // skip header
        $row = explode("\t", $line); 
        //print_r($row);
        $record_no =  trim($row[0]);
        $this->logger->debug("processing record_no $record_no");
        $partner = trim($row[1]);
        $pageURL = trim($row[2]);
        #$pageURL = substr(trim($row[2]),'"','');
        $pageName = trim($row[3]);
        $channel = trim($row[4]);
        $products = trim($row[5]);
        $events = trim($row[6]);
        $zip = trim($row[7]);
        $state = trim($row[8]);
        $purchaseID = trim($row[9]);
        $prop4 = trim($row[10]);
        $eVar3 = trim($row[11]);

        $this->logger->debug("pageURL=$pageURL");
        $this->logger->debug("pageName=$pageName");
        $sql = "INSERT INTO aaa_tag_partnermap 
          (no,partner,pageURL,pageName,channel,products,events,zip,state,purchaseID,prop4,eVar3)
          VALUES 
          ('$record_no','$partner','$pageURL','$pageName','$channel','$products','$events','$zip','$state','$purchaseID','$prop4','$eVar3')";
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

  function lookup_var_id($var_name) {
    try {
      if(!self::$dbh) $this->connect();
      $sql = "select var_id from aaa_tag_vars where var_name = '$var_name'";
      $this->logger->debug("sql = $sql");
      $sth = self::$dbh->prepare($sql);
      $sth->execute();
      $result = $sth->fetch(PDO::FETCH_ASSOC);
      $var_id = $result['var_id'];
      $this->logger->debug("var_id = $var_id");
    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
    }
    return $var_id;
  }


  function insert_varmap($page_id, $var_name, $var_setting) {
    try {
      if(!self::$dbh) $this->connect();

      $var_id = $this->lookup_var_id($var_name);
      $sql = "INSERT INTO aaa_tag_varmap(page_id, var_id, var_setting) values ($page_id, $var_id, :var_setting)";
      $this->logger->debug( "sql=$sql");
      $stmt = self::$dbh->prepare($sql);
      $stmt->bindParam(':var_setting', $var_setting, PDO::PARAM_STR);
      $ret = $stmt->execute();
    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
    }
    return $ret;
  }
  function display_partnermap($partner) {
    try {
      if(!self::$dbh) $this->connect();

      $sql = "SELECT * FROM aaa_tag_partnermap where partner = '$partner'";
      $this->logger->debug("sql=$sql");
      $result = self::$dbh->query($sql);
      $rows = $result->fetchAll();
      echo("<table>");
      foreach ($rows as $row) {
        $no =  trim($row[0]);
        $partner = trim($row[1]);
        $pageURL = substr(trim($row[2]),'"','');
        $pageName = trim($row[3]);
        $channel = trim($row[4]);
        $products = trim($row[5]);
        $events = trim($row[6]);
        $zip = trim($row[7]);
        $state = trim($row[8]);
        $purchaseID = trim($row[9]);
        $prop4 = trim($row[10]);
        $eVar3 = trim($row[11]);
        //print_r($row);
        echo("<tr>");
        echo("<td>$no</td>");
        echo("<td>$partner</td>");
        echo("<td>$pageURL</td>");
        echo("<td>$pageName</td>");
        echo("<td>$channel</td>");
        echo("<td>$products</td>");
        echo("<td>$events</td>");
        echo("<td>$zip</td>");
        echo("<td>$state</td>");
        echo("<td>$purchaseID</td>");
        echo("<td>$prop4</td>");
        echo("<td>$eVar3</td>");
        echo("</tr>");
      }
      echo("</table>");
     
    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
    }
    return $ret;
  }
 
  function populate_varmap($partner) {
    try {
      if(!self::$dbh) $this->connect();

      $sql = "SELECT * FROM aaa_tag_partnermap where partner = '$partner'";
      $this->logger->debug( "sql=$sql");
      $result = self::$dbh->query($sql);
      $rows = $result->fetchAll();
      foreach ($rows as $row) {
        $no =  trim($row[0]);
        $partner = trim($row[1]);
        $pageURL = substr(trim($row[2]),'"','');
        $pageName = trim($row[3]);
        $channel = trim($row[4]);
        $products = trim($row[5]);
        $events = trim($row[6]);
        $zip = trim($row[7]);
        $state = trim($row[8]);
        $purchaseID = trim($row[9]);
        $prop4 = trim($row[10]);
        $eVar3 = trim($row[11]);
        //print_r($row);
        $this->logger->debug("pageURL=$pageURL");
        if (!empty($pageURL)) $this->insert_varmap($no, 's.pageURL', $pageURL);
        if (!empty($pageName)) $this->insert_varmap($no, 's.pageName', $pageName);
        if (!empty($channel)) $this->insert_varmap($no, 's.channel', $channel);
        if (!empty($products)) $this->insert_varmap($no, 's.products', $products);
        //if (!empty($events)) $this->insert_varmap($no, 's.events', $events);
        if (!empty($zip)) $this->insert_varmap($no, 's.zip', $zip);
        if (!empty($state)) $this->insert_varmap($no, 's.state', $state);
        if (!empty($puchaseID)) $this->insert_varmap($no, 's.purchaseID', $puchaseID);
        if (!empty($prop4)) $this->insert_varmap($no, 's.prop4', $prop4);
        if (!empty($eVar3)) $this->insert_varmap($no, 's.eVar3', $eVar3);

      }
    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
    }
    return $ret;
  } 

  function reset_varmap($partner) {
    try {
      $this->logger->debug( "start reset_varmap");
      if(!self::$dbh) $this->connect();
      $sql1 = "delete from aaa_tag_varmap where page_id in (select page_id from aaa_tag_pages pg, aaa_tag_partners pn where pn.partner_id = pg.partner_id and pn.partner_name = '$partner')";
      $this->logger->debug( "sql1=$sql1");
      $stmt = self::$dbh->prepare($sql1);
      $ret = $stmt->execute();
      $sql2 = "delete from aaa_tag_pages where partner_id in (select partner_id from aaa_tag_partners where partner_name = '$partner')";
      $this->logger->debug( "sql2=$sql2");
      $stmt = self::$dbh->prepare($sql2);
      $ret = $stmt->execute();
      $sql3 = "INSERT INTO aaa_tag_pages(page_id, page_url, partner_id) select no, pageURL, partner_id from aaa_tag_partnermap m, aaa_tag_partners p where p.partner_name = m.partner and m.partner = '$partner'"; 
      $this->logger->debug( "sql3=$sql3");
      $stmt = self::$dbh->prepare($sql3);
      $ret = $stmt->execute();
      $this->populate_varmap($partner);
      $this->logger->debug( "finish reset_varmap");
    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
    }
    return $ret;
  }
}
?>
