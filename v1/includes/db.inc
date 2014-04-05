<?php
class db {
  protected static $dbh = false;

  function connect() {
    $db_url = "mysql:host=localhost;dbname=aaametrics";
    self::$dbh = new PDO($db_url,"aaametrics","aaametrics123");
    self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  protected function fatal_error($msg) {
    echo "<pre>Error!: $msg\n";
    $bt = debug_backtrace();
    foreach($bt as $line) {
      $args = var_export($line['args'], true);
      echo "{$line['function']}($args) at {$line['file']}:{$line['line']}\n";
    }
    echo "</pre>";
    die();
  }
}
?>
