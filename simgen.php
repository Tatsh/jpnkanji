<?php

error_reporting  (E_ERROR | E_WARNING | E_PARSE);

# This php program generates the kanji similarity tables.
# It must be run after kanjikonverto.php

include 'konvertfun.php';

mysql_connect('chii', 'root', '');
mysql_select_db('japdict');

function Table($s)
{
  switch($s)
  {
    case 'kanjiparts':
    case 'kanjiradstrokes':
    case 'kanjisimilarity':
      return $s;
    default:
      return $s;
  }
}

function CompareKanji($jis1, $jis2)
{
  /* count(partcode)a: osien määrä yhteensä, esim. 5+5
   * count(distinct partcode)b: erilaisten osien määrä, esim. 4
   * huono tilanne: b lim a
   * hyvä tilanne: b lim a/2
   */
/*  $SQL = 'select (count(distinct partcode)) / count(partcode)'.
         ' from kanjiparts where jiscode='.$jis1.' or jiscode='.$jis2;
  $tmp = mysql_fetch_row(mysql_query($SQL));
  return $tmp[0];
*/
  global $parts, $strokesJ, $strokesP;
  
  $parts1 = &$parts[$jis1];
  $parts2 = &$parts[$jis2];
  
  $strok1 = $strokesJ[$jis1];
  $strok2 = $strokesJ[$jis2];
  
  $common = array_intersect($parts1, $parts2);
  $commonstrokes = 0;
  foreach($common as $j)
    $commonstrokes += $strokesP[$j];
  
  $strokp1 = $commonstrokes / $strok1;
  $strokp2 = $commonstrokes / $strok2;
  return ($strokp1 + $strokp2) / 2;
  
  //$commoncount = count($common);
  //$pros1 = $commoncount / count($parts1);
  //$pros2 = $commoncount / count($parts2);
  //return ($pros1 + $pros2) / 2;
/**/
}

print "Loading data...\n"; ob_end_flush(); flush();

$tmp = mysql_query('select a.partcode,jiscode,strokes'.
                   ' from '.Table('kanjiparts').' a,'.Table('kanjiradstrokes').' b'.
                   ' where a.partcode=b.partcode');
$alljis = array();
$parts = array();
$strokes = array();
while(($tmp2 = mysql_fetch_array($tmp)))
{
  $parts[$tmp2['jiscode']][] = $tmp2['partcode'];
  $strokesJ[$tmp2['jiscode']] += $tmp2['strokes'];
  $strokesP[$tmp2['partcode']] = $tmp2['strokes'];
}
unset($tmp2);
unset($tmp);

foreach($parts as $j => $p)
  $alljis[] = $j;

$max = count($alljis);

print "Generating kanji similarity tables...\n"; ob_end_flush(); flush();

$pos = 0;
$prositer = 100 / $max;
$pros = 0;

$fo = fopen('simgen.sql', 'w');
mysql_query('delete from '.Table('kanjisimilarity'));
$c=0;
$start = time();
for($a=0; $a<$max; $a++)
{
  $aa = $alljis[$a];
  $elap = time()-$start;
  $eta = $max * $elap / ($a+1) - $elap;
  printf("%5.2f%% - eta %ds    \r", $pros, $eta); ob_end_flush(); flush();
  $pros += $prositer;

  foreach($alljis as $ab)
  {
    $res = CompareKanji($aa, $ab);
    if($res > 0.2)
    {
      $code = 255 - (int)(255*($res-0.2)/(1-0.2));
      
      if(!$c)$SQL = 'insert into '.Table('kanjisimilarity').'(jiscode1,jiscode2,unsimilarity)values';
      $SQL .= ',('.$aa.','.$ab.",$code)";
      if($c++ > 200)
      {
        $SQL = str_replace('values,(', 'values(', $SQL);
        fwrite($fo, "$SQL\n");
        #mysql_insert_background($SQL);
        $SQL = '';
        $c = 0;
      }
    }
  }
}
if($c)
{
  // Fix the last entry
  $SQL = str_replace('values,(', 'values(', $SQL);
  fwrite($fo, "$SQL\n");
  #mysql_insert_background($SQL);
}
fclose($fo);
mysql_background_end();
