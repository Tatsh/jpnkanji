<?php
/* This file is maintained in ~/src/japkanji/ - do not modify otherwhere */

header('Content-type: text/html; charset=utf-8');

include '/WWW/cache.php';
include '/WWW/utf8.php';

if(cache_init(3600*24*30, array('uri'=> $REQUEST_URI,'time'=>filemtime('japkanjilist.php'))))
  cache_dump_exit();

echo '<h1>KANJI</h1>';
echo '<center>Complete list of all kanji in <a href="http://www.unicode.org/">unicode</a> (U4DF8 - U9FB0)</center>';

echo '<pre style="font-size:200%">';
for($c=19960; $c<=40880; $c++)
{
  if($c % 20 == 0)printf("%5d:", $c);

  $s = utf8str($c);
  
  print '<a href="japkanji.php?s=';
  print urlencode($s);
  echo '">';
  print $s;
  echo '</a>';
  if(($c % 20) == 19)print "\n";
}
echo '</pre>';

cache_end();
