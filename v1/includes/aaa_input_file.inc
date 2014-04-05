<?php
class aaa_input_file extends db {
  function load() {
    $where = '';
    try {
      if(!self::$dbh) $this->connect();
      $result = self::$dbh->query("select * from aaa_input_file
          $where order by row_num");
      $rows = $result->fetchAll(PDO::FETCH_ASSOC); 
    } catch (PDOException $e) {
      $this->fatal_error($e->getMessage());
    }
    return $rows;
  }
}
?>
