<?php
$link = mysql_connect('localhost', 'aaametrics', 'aaametrics123');
if (!$link) {
  die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db('aaametrics', $link);
if (!$db_selected) {
      die ('Can\'t use aaametrics : ' . mysql_error());
}
$query = "select max(tag_id) tag_id from aaa_tag_tags";
$result = mysql_query($query);
if (!$result) {
  $message  = 'Invalid query: ' . mysql_error() . "\n";
  $message .= 'Whole query: ' . $query;
  die($message);
}

$row = mysql_fetch_assoc($result);
$tag_id = $row['tag_id'];

$query = "select max(page_id) page_id from aaa_tag_pages";
$result = mysql_query($query);
if (!$result) {
  $message  = 'Invalid query: ' . mysql_error() . "\n";
  $message .= 'Whole query: ' . $query;
  die($message);
}
$row = mysql_fetch_assoc($result);
$page_id = $row['page_id'];

$query = "select * from aaa_tag_dart_stg";

$result = mysql_query($query);
if (!$result) {
  $message  = 'Invalid query: ' . mysql_error() . "\n";
  $message .= 'Whole query: ' . $query;
  die($message);
}



while ($row = mysql_fetch_assoc($result)) {
  $tag_id++;
  $page_id++;
  $type= $row['d_type'];
  $cat = $row['d_cat'];
  $tag_name = $row['d_tag_name'];
  $page_name=$row['d_page_name'];
  $page_url=$row['d_page_url'];
  $script = 'var axel=Math.random();var a=axel*10000000000000;var iframe=document.createElement("iframe");iframe.src="http://fls.doubleclick.net/activityi;src=2331885;type='.$type.';cat='.$cat.';ord="+a+"?";iframe.width=1;iframe.height=1;iframe.borderframe=0;document.body.append(iframe);';

  print ("insert into aaa_tag_tags_stg(tag_id, tag_script,tag_type, tag_name, agency_id) values($tag_id,'$script','iframe','$tag_name',6);\n");
  print ("insert into aaa_tag_dart_url_stg(page_id,tag_name,page_name,page_url) values($page_id,'$tag_name','$page_name','$page_url');\n");

//  print ($script . "\n");
}
mysql_free_result($result);
//echo 'Connected successfully'. "\n";
mysql_close($link);
?>
