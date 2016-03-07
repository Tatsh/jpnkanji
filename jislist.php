<?php

/* This file is maintained in ~/src/japkanji/ - do not modify otherwhere */

/*
  Settings:
    Omit these:
      0x2121..0x2137
      0x2139..0x2840
      0x2841..0x2C7E ?
      0x2D7D..0x2F7E ?
      0x4F54..0x4F7E ?
      0x7427..0x787E ?
      0x7D21..0x7E7E ?
      
*/

$mtime = filemtime('jislist.php');
#if(strtotime($HTTP_SERVER_VARS['HTTP_IF_MODIFIED_SINCE']) >= $mtime)
#{
#  header('HTTP/1.0 304 Not modified');
#  exit;
#}
header('Last-Modified: '. gmdate('D, d M Y H:i:s', $mtime).' GMT');

include_once 'gzcompress.php';
#include 'japcharset.php';
include_once 'smallcache.php';
include_once 'kanjisqlfun.php';

header('Content-type: text/html; charset=euc-jp');

?><style type="text/css"><!--
td
{
  white-space:nowrap;
  vertical-align:top;
  text-align:left;
  font-family:"ms gothic,sans-serif";
}
/* number */
span.a
{
  font-stretch:narrower;
  font-weight:bold;
  font-family:"ms gothic";
}
/* jouyou grade */
span.J
{
  color:blue;
  font-weight:lighter;
  font-size:80%;
  padding-left:3px
}
table a {text-decoration:none;color:black;background:white}
body {background:white;color:black}
--></style><?

$c = new Cache('jislist'.$all, $mtime);
if(!$c->DumpOrLog())
{
  for($pass = 0; $pass < 2; $pass++)
  {
    switch($pass)
    {
      case 0:
        $s = '';
        break;
      case 1:
       ob_start();
       passthru('echo '.shellfix($s).'|/usr/local/bin/htmlrecode -Ieuc-jp -Outf8 -l 2>/dev/null');
       $s = ob_get_contents();
       ob_end_clean();
       $s = explode("\n", $s);
       $k = array();
       $ri=0;
    }
    
    for($a=0x21; $a<=0x7E; ++$a)
      for($b=0x21; $b<=0x7E; ++$b)
      {
        $code = $a*256 + $b;
        if(!$all && (($code >= 0x2121 && $code <= 0x2837)
                  || ($code >= 0x2139 && $code <= 0x2840)
                  || ($code >= 0x2841 && $code <= 0x2C7E)
                  || ($code >= 0x2D7D && $code <= 0x2F7E)
                  || ($code >= 0x4F54 && $code <= 0x4F7E)
                  || ($code >= 0x7427 && $code <= 0x787E)
                  || ($code >= 0x7D21 && $code <= 0x7E7E)
          )         )
        {
          // Symbols, fullwidth latin, katakana, hiragana,
          // greek, cyrillic, box drawings
          continue;
        }
        if($pass==0)
          $s .= chr($a+128) . chr($b+128) . "\n";
        elseif($pass==1)
        {
          if($s[$ri] != '��') // utf-8 nonchar-nonchar
          {
            $tmp = mysql_query('select strokecount1,radb,jouyougrade from japkanji where jiscode='.$code);
            $tmp2 = mysql_fetch_array($tmp);
            
            if(!$tmp2)
              $grade = 0;
            else
              $grade = (int)$tmp2['jouyougrade'];
            
            if(!$grade)$grade = 10;
            
            $grades = array
            (
             10 => 'x', 
              1 => '1',
              2 => '2',
              3 => '3',
              4 => '4',
              5 => '5',
              6 => '6',
              8 => 'u', //general use
              9 => 'n', //names
            );
            
            $k[] = array
            (
              'text' =>
                sprintf('<td><a href="%s"><span class=a>%04X</span></a> %c%c<span class=J>%s</span></td>',
                        PickLink($code),
                        $code, $a+128,$b+128, $grades[$grade]),
              'code' => $code,
              'bushu' => $tmp2['radb'],
              'strokes' => $tmp2['strokecount1'],
              'jouyou' => $grade
            );
          }
          $ri++;
        }
      }
  }
  function kcmp($a, $b)
  {
    global $all;
    if(!$all)
    {
      if($a['jouyou'] < $b['jouyou'])return -1;
      if($a['jouyou'] > $b['jouyou'])return 1;
    }
    #if($a['bushu'] < $b['bushu'])return -1;
    #if($a['bushu'] > $b['bushu'])return 1;
    #if($a['strokes'] < $b['strokes'])return -1;
    #if($a['strokes'] > $b['strokes'])return 1;
    if($a['code'] < $b['code'])return -1;
    if($a['code'] > $b['code'])return 1;
    return 0;
  }
  usort($k, kcmp);
  
  $riveja = 55;
  $sara = 9;
  $size = count($k);
  
  $maarapersivu = $riveja * $sara;
  $sivuja = (int)($size / $maarapersivu + 0.999999);
  
  for($sivu=0; $sivu<$sivuja; ++$sivu)
  {
    $alku = $sivu * $maarapersivu;
    $loppu = ($sivu+1) * $maarapersivu-1;
    if($loppu >= $size)$loppu = $size-1;
    $maara = $loppu-$alku+1;
    
    echo "[ Sivu ", ($sivu+1), " / ", $sivuja, ' ]<br>';
    
    $space = (int)($maara / $sara);
    $len   = (int)($maara / $space + ($space-1));
    
    print '<table cellspacing=0 cellpadding=0 width="100%">';
    
    $rivit = (int)($maara / $sara + 0.999999);
    
    for($rivi=0; $rivi<$rivit; $rivi++)
    {
      print '<tr>';
      $pos = $alku + $rivi;
      for($s=0; $s<$sara; ++$s)
      {
        print $k[$pos]['text'];
        $pos += $rivit;
      }
      print '</tr>';
    }
    
    print '</table>';
    #if($sivu > 0)print '<br>';
  }
  ?>
   <p>
    Total <?=count($k)?> kanji.<br>
    Jouyou grades, kanji classifications:
    <span class=J>1,2,3,4,5,6</span> = grades 1-6 (for the ~1000 most used kanji)<br>
    <span class=J>u</span> = general use
    &nbsp;&nbsp;&nbsp;
    <span class=J>n</span> = jinmeiyou (for use in names)
    &nbsp;&nbsp;&nbsp;
    <span class=J>x</span> = unclassified
    <br>
    Use the numbers: http://kanjidict.stc.cx/NNNN
  <?
  print '<p>';
}
$c->_Cache();
unset($c);

#CharSetEnd('euc-jp', 'iso-8859-1');
GzCompressEnd();
