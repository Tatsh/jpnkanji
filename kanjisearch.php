<?php

/* This file is maintained in ~/src/japkanji/ - do not modify otherwhere */

#print '<h1>DICTIONARY DATABASE UNDER RECONSTRUCTION, COME BACK IN 24 HOURS</h1>Message left Fri Mar 18 14:05:26 EET 2005';
#exit;

if(!$REMOTE_ADDR)
{
  foreach(explode('&', $_SERVER['argv']) as $var=>$value)
    $$var=$value;
  $REQUEST_URI .= $_SERVER['argv'];
}

/* Register globals (I know, this is not a good thing) */
foreach($_GET as $var=>$value)
  $$var = $value;

if(!$outset)
  $outset = 'utf-8';

if(!$noctheader)
  header('Content-type: text/html; charset='.$outset);

require 'gzcompress.php';
require 'kanjisqlfun.php';
require 'unique2.php';

include '/WWW/headers.php';

$hdrs .= '<meta http-equiv="Content-Type" content="text-html; charset=utf-8">'.
         '<meta name="robots" content="nofollow,noindex">';

$pagetitle = 'Kanjisearch';

/* Fields that can be used in ordering */
$orderfields = array
(
  'radb'         => 'Bushu radical',
  'radc'         => 'Classical radical',
  'jouyougrade'  => 'Jouyou grade',
  'freqorder'    => 'Frequency order',
  'strokecount1' => 'Stroke count',
  'unsimilarity' => 'Unsimilarity'
);

/* Different translations */
$translations = array
(
  'china'   => 'Chinese (pinyin)',
  'korea'   => 'Korean',
  'english' => 'English'
);


/* User preferences */
$preferences = array
(
  'searchorder'  => 'freqorder',
  'similars'     => array('radb'        => 'freqorder',
                          'radc'        => 'freqorder',
                          'jouyougrade' => 'freqorder',
                          'similarity'  => 'unsimilarity,freqorder'
                         ),
  'max' => array('similars'  => 200,
                 'results'   => 4000)
);
foreach($translations as $a => $b)
  $preferences['translations'][$a] = 1;

/* Possible similars to find */
$similarclasses = array
(
  'radb'         => 'Other similar kanji (same Bushu radical)',
  'radc'         => 'Other similar kanji (same Classical radical)',
  'jouyougrade'  => 'Other kanji of the same jouyou grade',
  'strokecount1' => 'Other kanji with the same stroke count',
  'similarity'   => 'Other similar kanji (graphical shapes)'
);


ob_start();

/* BEGIN */

function tableformat($type, $tableclass,$thclass,$tdclass, $entries)
{
  $s = '<TABLE CLASS='.$tableclass.'>';
  switch($type)
  {
    case 0:
      foreach($entries as $th=>$td)
        $s .= '<TR><TH CLASS='.$thclass.'>'.$th.'</TH><TD CLASS='.$tdclass.'>'.$td.'</TD></TR>';
      break;
    case 1:
      $s .= '<TR>';
      foreach($entries as $th=>$td)
        $s .= '<TH CLASS='.$thclass.'>'.$th.'</TH><TD CLASS='.$tdclass.'>'.$td.'</TD>';
      $s .= '</TR>';
      break;
    case 2:
      $s .= '<TR>';
      foreach($entries as $th=>$td)$s .= '<TH CLASS='.$thclass.'>'.$th.'</TH>';
      $s .= '</TR><TR>';
      foreach($entries as $th=>$td)$s .= '<TD CLASS='.$tdclass.'>'.$td.'</TD>';
      $s .= '</TR>';
      break;
  }
  $s .= '</TABLE>';
  return $s;
}

function clearaliases() { global $aliases; $aliases = array(); }
function getalias($s)
{
  global $aliases;
  if(ereg('^0x', $s))return strtoupper(dechex(getalias(substr($s,2))));
  return $aliases[$s];
}
function setalias($field, $content)
{
  global $aliases;
  $aliases[$field] = $content;
}


include_once 'smallcache.php';

function ShowSimilars($SQL)
{
  $c = new BinKeyCache($SQL, 1);
  if($c->DumpOrLog())return;
  
  $array = mysql_fetch_all(mysql_query($SQL));
  foreach($array as $tmp3)
  {
    echo ' <a href="', PickLink($tmp3['jiscode']), '">',
         $tmp3['utf8kanji'], '</a>';
  }
  
  $c->_Cache();
}

function IsPrinting()
{
  return (int)$_GET['print'];
}

class SearchMethod
{
  function GetVars() {}
  function FormTitle() {}
  function ResultsTitle() {}
  function DisplayForm() {}
  function GetCacheKey()
  {
    $vars = array_unique($this->GetVars());
    $s = '';
    foreach($vars as $varname)
      $s .= sprintf('%08X%08X', crc32($varname), crc32($_GET[$varname]));

    $s .= sprintf('%02X', IsPrinting());
    return $s;
  }
  function GetFullSQL()
  {
    global $preferences;
    $orderby = $preferences['searchorder'].',jiscode';

    return 'select jiscode,utf8kanji,mnemonic from japkanji where '
           . $this->GetSQL() . ' order by ' . $orderby;
  }
  function DisplayResults()
  {
    $s = $this->GetCacheKey();
    if(strlen($s))
    {
      #print 'Testing cache key "'.$s.'"';
      $cache = new BinKeyCache($s, 3);
      if($cache->DumpOrLog())return;
    }

    $SQL = $this->GetFullSQL();

    $data = array();
    if(IsPrinting() > 0)
    {
      echo '<SPAN CLASS=searchresults>', $this->ResultsTitle(), ':<BR></SPAN>';
      echo '<small>DISCLAIMER: Not all possible readings are shown from the kanji. ',
           'The way the list is shortened is in no way official nor generally acknowledged.',
           '</small>';

      print '<table border=1>';
      print '<tr>';
      for($k=0; $k<2; $k++)
      {
        print '<th>Kanji</th>';
        print '<th>Readings</th>';
        print '<th>English</th>';
      }
      print '</tr>';
      $c=0;
      $trbuf='';
      foreach(mysql_fetch_all(mysql_query($SQL)) as $tab)
      {
        $trans = mysql_fetch_all(mysql_query(
          'select name from kanjitrans where jiscode='.$tab['jiscode'].' order by id'));
        $japan = mysql_fetch_all(mysql_query(
             'select kana,kanar from kanjijapan where jiscode='.$tab['jiscode'].
             ' and type<>1'. /* strip nanori */
             ' order by type,kanar'));
        /* Pisteen j�lkeiset pois */
        foreach($japan as $kk => $ab)
        {
          $japan[$kk]['kana']  = ereg_replace('\\..*', '-', $ab['kana']);
          $japan[$kk]['kanar'] = ereg_replace('\\..*', '-', $ab['kanar']);
        }
        $ekattavut = array();
        foreach($japan as $ab)
        {
          $s = $ab['kana'];
          $kk = '';
          /* These lists are generated like this:
              /WWW/hiragana kakikukeko|sed 's/;/;|/g'|charconv unihtml utf8
           */
          /**/if(ereg('^(か|き|く|け|こ)', $s))$kk = 'k';
          elseif(ereg('^(が|ぎ|ぐ|げ|ご)', $s))$kk = 'g';
          elseif(ereg('^(た|ち|つ|て|と)', $s))$kk = 't';
          elseif(ereg('^(だ|ぢ|づ|で|ど)', $s))$kk = 'd';
          elseif(ereg('^(は|ひ|ふ|へ|ほ)', $s))$kk = 'h';
          elseif(ereg('^(ば|び|ぶ|べ|ぼ)', $s))$kk = 'b';
          elseif(ereg('^(ぱ|ぴ|ぷ|ぺ|ぽ)', $s))$kk = 'p';
          elseif(ereg('^(さ|し|す|せ|そ)', $s))$kk = 's';
          elseif(ereg('^(ざ|じ|ず|ぜ|ぞ)', $s))$kk = 'z';
          $ekattavut[$kk] = 1;
        }
        foreach(array('g'=>'k', 'd'=>'t', 'b'=>'h', 'p'=>'h', 'z'=>'s') as $g=>$kk)
          if($ekattavut[$g] && $ekattavut[$kk])
            foreach($japan as $kk => $ab)
              if(ereg("^$g", $ab['kanar'])) unset($japan[$kk]);
        foreach($japan as $kk => $ab)
        {
          unset($japan[$kk]['kanar']);
          unset($japan[$kk][0]);
          unset($japan[$kk][1]);
        }
        
        // array_unique doesn't work when the contents are an array.
        //$japan = array_unique($japan);
        $japan = array_unique2($japan);
        

/*
24.5.2002
1337#gurb@slobber 1) Kaikki name-luokkaa olevat pois, 2) Kaikki pisteen j�lkeiset tavarat
+                 sanasta pois (ja pisteen tilalle vaikka - ett� se jatkuu) ja tietenkin
+                 poista duplikaatit, 3) jos ensimm�isest� tavusta on sek� aksentillista (''
+                 tai o) versiota ett� aksentitonta, ota aksentillinen/-set pois jotka l�ytyy
+                 my�s aksentittomina
*/
        
        if(!$c)print '<tr>';
         echo '<th valign=top width="5%"';
         if(IsPrinting() == 3)echo ' rowspan=2';
         echo '><span class=printkanji>';
          /* Kanji */
          echo ' <a href="', PickLink($tab['jiscode']), '">', $tab['utf8kanji'], '</a>';
         echo '</span></th>';
         
         if(IsPrinting() == 3)
         {
           echo '<td valign=top colspan=2 width="45%" align=right><small>',
                htmlspecialchars($tab['mnemonic']), '</small></td>';
           ob_start();
         }
         
         echo '<td valign=top width="30%"><span class=kana>';
          /* Readings */
          $p=0;
          foreach($japan as $ab)
            if(strlen($ab['kana']))
            {
              if($p++)echo ",\n"; // "&#12289;\n";
              echo $ab['kana'];
            }
         echo '</span></td>';
         echo '<td valign=top width="15%"><small>';
          /* English */
          $p=0;
          foreach($trans as $ab)
            if(strlen($ab['name']))
            {
              if($p++)echo ', ';
              echo htmlspecialchars($ab['name']);
            }
         echo '</small></td>';
        
        if(IsPrinting() == 3)
        {
          $trbuf .= ob_get_contents();
          ob_end_clean();
        }
        
        if(++$c>=$k)
        {
          $c=0;print '</tr>';
          if(IsPrinting() == 3) print "<tr>$trbuf</tr>";
          $trbuf = '';
        }
      }
      if($c)
      {
        print '</tr>';
        if(IsPrinting() == 3) print "<tr>$trbuf</tr>";
        $trbuf = '';
      }
      print '</table>';
    }
    else
    {
      echo '<SPAN CLASS=searchresults>', $this->ResultsTitle(), ':<BR>';
      
      foreach(mysql_fetch_all(mysql_query($SQL)) as $tab)
      {
        echo ' <a href="', PickLink($tab['jiscode']), '">', $tab['utf8kanji'], '</a>';
      }
    }
    
    echo '</SPAN>';
    
    if(IsPrinting() == 0)
    {
      print '<ul>';
       echo '<li><a href="'.htmlspecialchars('search?'.$_SERVER['QUERY_STRING'].'&print=1');
       echo '">Print the above results as a table</a>';
       echo '</li>';
       echo '<li><a href="'.htmlspecialchars('search?'.$_SERVER['QUERY_STRING'].'&print=1&outset=euc-jp');
       echo '">Print, also works when your printer does not quite support unicode</a>',
        ' (thanks <a href="http://web.lfw.org/shodouka/">Shodouka</a>!)';
       echo '</li>';
      print '</ul>';
    }

    if(isset($cache))
      $cache->_Cache();
  }
  function HaveResults() {}
  function GetSQL() {}
};

$searchmethods = array();
function AddSearchMethod($key, $object)
{
  global $searchmethods;
  
  $searchmethods[$key] = array
  (
    'desc' => $object->desc,
    'obj'  => $object
  );
}

include 'searchmodules/skip.php';
include 'searchmodules/kanjiinput.php';
include 'searchmodules/fourcorner.php';
include 'searchmodules/criter.php';
include 'searchmodules/customize.php';
include 'searchmodules/component.php';

function SelectSearch()
{
  global $searchmethods;
  
  $choices = array();
  foreach($searchmethods as $param => $array)
    $choices[$param] = $array['desc'];
  
  print '<h2>Select your kanji search method.</h2>';
  print '<ul>';
  foreach($choices as $param => $desc)
  {
    $url = $param.'search';
    echo '<li><a href="', htmlspecialchars($url), '">',
              htmlspecialchars($desc), '</a></li>';
  }
  print '</ul>';
  echo '<hr>',
   'Written by <a href="http://bisqwit.iki.fi/">Bisqwit</a>, ',
   'based on Jim Breen\'s Kanjidic.<br>',
   'For more information please see: ',
   '<a href="http://www.csse.monash.edu.au/~jwb/japanese.html">Jim Breen\'s Homepage</a><br>';
}

function CompareKanji($jis1, $jis2)
{
  $SQL = 'select (count(partcode)-count(distinct partcode)) / count(partcode)'.
         ' from kanjiparts where jiscode='.$jis1.' or jiscode='.$jis2;
  $tmp = mysql_fetch_row(mysql_query($SQL));
  return $tmp[0];
/*
  $parts1 = mysql_fetch_all(mysql_query('select partcode from kanjiparts where jiscode='.$jis1));
  $parts2 = mysql_fetch_all(mysql_query('select partcode from kanjiparts where jiscode='.$jis2));
  
  $common = array_intersect($parts1, $parts2);
  
  $pros1 = count($common) / count($parts1);
  $pros2 = count($common) / count($parts2);
  
  $pros = ($pros1 + $pros2) / 2;
  return $pros;
*/
}

function Show($tmp2)
{
  global $preferences, $similarclasses;
  
  $display = array();
  $display['aliases'] = array
  (
    'skip'        => '@skip1-@skip2-@skip3',
    'fourcorner'  => '@fourcorner1 / @fourcorner2',
    'strokecount' => '@strokecount1 / @strokecount2 / @strokecount3',
    'indexo'      => '@indexo1 / @indexo2'
  );
  $display['display'] = 
      '
  <DIV CLASS="left">'.tableformat(0,'ind','ind','ind',array(
     'Halpern index' =>     '@indexh',
     'Nelson index' =>      '@indexn',
     'Haig index' =>        '@indexv',
     'Henshall index' =>    '<a href="http://www.joyo96.org/cgi-bin/henshall.pl?hen=@indexe">@indexe</a>',
     'Gakken index' =>      '@indexk',
     'Heisig index' =>      '@indexl',
     'O\'Neill index' =>    '@indexo',
     'Unicode'        =>    'U@0xunicode'
   )).'
  </DIV>
  <DIV CLASS="right">@kanalist</DIV>
  <TABLE><TR><TD VALIGN=TOP>
   <DIV CLASS="kanji">
   JIS# @0xjiscode<BR>
   <SPAN CLASS=kanji>@utf8kanji</SPAN><BR>
   <SPAN CLASS=parts>Components:<BR>@parts</SPAN>
   </DIV>
  </TD>';
  
 if($preferences['translations']['china']
 || $preferences['translations']['korea']
 || $preferences['translations']['english'])
 {
   $display['display'] .= 
    '<TD VALIGN=TOP>
     <TABLE>';
   
   if($preferences['translations']['china']
   || $preferences['translations']['english'])
   {
     $display['display'] .= '<TR>';
     
     if($preferences['translations']['china'])
     {
       $display['display'] .= '<TD VALIGN=TOP>@chinalist</TD>';
     }

     if($preferences['translations']['english'])
     {
       $display['display'] .= '<TD VALIGN=TOP ROWSPAN=2>@english</TD>';
     }

     $display['display'] .= '</TR>';
   }

   if($preferences['translations']['korea'])
   {
     $display['display'] .=
       '<TR>
        <TD VALIGN=TOP>@korealist</TD>
       </TR>';
   }
   
   $display['display'] .=
    '</TABLE></TD>';
 }
 $display['display'] .= 
  '</TR></TABLE>
  '.tableformat(2,'misc','misc','misc',array(
    'Radical'      => '@radb / @radc',
    'SKIP'         => '@skip',
    '4corner'      => '@fourcorner',
    'Freq.'        => '@frequency',
    'Grade'        => '@jouyougrade',
    '#Strokes'     => '@strokecount')).'
  ';

  clearaliases();
  foreach($tmp2 as $field=>$content)
    setalias($field, $content);
    
  setalias('utf8kanji', makedictlink($tmp2['utf8kanji']));
  
  $tmp3 = mysql_query('select partcode from kanjiparts where jiscode='.$tmp2['jiscode']);
  $s = '';
  while(($tmp4 = mysql_fetch_array($tmp3)))
  {
    $tmp5 = mysql_query('select utf8kanji from japkanji where jiscode='.$tmp4['partcode']);
    $tmp6 = mysql_fetch_row($tmp5);
    if($tmp6)$s .= $tmp6[0];
  }
  setalias('parts', $s);
  
  $korea = mysql_fetch_all(mysql_query('select romaji from kanjikorea where jiscode='.$tmp2['jiscode'].' order by id'));
  $china = mysql_fetch_all(mysql_query('select romaji from kanjichina where jiscode='.$tmp2['jiscode'].' order by id'));
  $japan = mysql_fetch_all(mysql_query('select kana,kanar,type from kanjijapan where jiscode='.$tmp2['jiscode'].' order by id'));
  $trans = mysql_fetch_all(mysql_query('select name from kanjitrans where jiscode='.$tmp2['jiscode'].' order by id'));
  
  $s = '<TABLE CLASS=kanalist>';
  $s .= '<TR><TH CLASS=kanalist>kana</TH>'
           .'<TH CLASS=kanalist>romaji</TH>'
           .'</TR>';
  for($type=0; $type<=2; $type++)
  {
    switch($type)
    {
      case 0: $typetxt = 'Reading'; break;
      case 1: $typetxt = 'Name (nanori)'; break;
      case 2: $typetxt = 'Radical name'; break;
    }
    $c=0;
    foreach($japan as $tab)if($tab['type']==$type)$c++;
    foreach($japan as $tab)
    {
      if($tab['type']==$type)
      {
        $s .= '<TR>';
        if($c)
        {
          $s .= '<TD CLASS="kanalist type" COLSPAN=2>'.$typetxt.'</TD></TR><TR>';
          $c = 0;
        }
        $s .= '<TD CLASS="kanalist kana">'.makedictlink($tab['kana']).'</TD>'
             .'<TD CLASS="kanalist romaji">'.$tab['kanar'].'</TD>';
        $s .= '</TR>';
      }
    }
  }
  $s .= '</TABLE>';
  setalias('kanalist', $s);

  $s = '<TABLE CLASS=korealist>';
  $s .= '<TR><TH CLASS=korealist>Korean</TH></TR>';
  foreach($korea as $tab)$s .= '<TR><TD CLASS="korealist">'.$tab['romaji'].'</TD></TR>';
  $s .= '</TABLE>';
  setalias('korealist', $s);

  $s = '<TABLE CLASS=chinalist>';
  $s .= '<TR><TH CLASS=chinalist>Mandarin</TH></TR>';
  foreach($china as $tab)$s .= '<TR><TD CLASS="chinalist">'.$tab['romaji'].'</TD></TR>';
  $s .= '</TABLE>';
  setalias('chinalist', $s);

  $s = '<TABLE CLASS=english>';
  $s .= '<TR><TH CLASS=english>English</TH></TR>';
  foreach($trans as $tab)$s .= '<TR><TD CLASS="english">'.$tab['name'].'</TD></TR>';
  $s .= '</TABLE>';
  setalias('english', $s);

  foreach($display['aliases'] as $alias => $content)
  {
    $content = preg_replace('|@([A-Za-z0-9]+)|e', 'getalias("\1")', $content);
    
    $content = str_replace('/ 0', '', $content);
    $content = ereg_replace('/ $', '', $content);
    
    setalias($alias, $content);
  }
  $content = preg_replace('|@([A-Za-z0-9]+)|e', 'getalias("\1")', $display['display']);
  print $content;
  
  foreach($similarclasses as $field => $desc)
    if($tmp2[$field] || $field=='similarity')
    {
      $orderby = $preferences['similars'][$field];
      if($orderby)
      {
        echo htmlspecialchars($desc), ':';
        echo '<SPAN CLASS=similar>';
        if($field == 'similarity')
        {
          $SQL = 'select jiscode,utf8kanji,unsimilarity'.
                ' from kanjisimilarity,japkanji'.
                ' where jiscode1='.$tmp2['jiscode'].
                ' and japkanji.jiscode<>'.$tmp2['jiscode'].
                ' and jiscode2=japkanji.jiscode'.
                ' order by '.$orderby.
                ' limit 150';
        }
        else
          $SQL = 'select jiscode,utf8kanji from japkanji where '.$field.'='.$tmp2[$field].' and jiscode<>'.$tmp2['jiscode'].
                 ' order by '.$orderby.',jiscode';
        ShowSimilars($SQL);
        print '</SPAN><br>';
      }
    }
  
  print '<hr>';
}

$noselectsearch = 0;
if(IsPrinting() > 0)
  $noselectsearch = 1;

foreach($searchmethods as $param => $array)
{
  if($_GET['search'] == $param)
  {
    $pagetitle .= ' - ' . $array['obj']->FormTitle();
    echo '<h2>', $array['obj']->FormTitle(), '</h2>';
    echo '<form method=GET action="search">';
    $array['obj']->DisplayForm();
    echo '</form>';
    $noselectsearch = 1;
  }
  if($array['obj']->HaveResults())
  {
    $pagetitle .= ' - ' . $array['obj']->ResultsTitle();
    $array['obj']->DisplayResults();
  }
}

if($pick)
{
  $SQL = 'select * from japkanji where jiscode='.hexdec($pick);
  foreach(mysql_fetch_all(mysql_query($SQL)) as $tmp3)
  {
    $pagetitle .= ' - '.$tmp3['utf8kanji'];
    Show($tmp3);
  }
}

if(!$noselectsearch)
{
  SelectSearch();
}

/* END */

$page = ob_get_contents();
ob_end_clean();

$omitmenu = 1;

if($outset != 'utf-8')
{
  ob_start();
}

print "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";

if(IsPrinting() > 0)
{
  echo '<HTML><HEAD><TITLE>', htmlspecialchars($title), '</TITLE>',
       '<link rel=stylesheet href="css" type="text/css">',
       "<STYLE TYPE=\"text/css\"><!--\n",
       "A{text-decoration:none;color:#000030;font-size:120%}",
       "\n--></STYLE></HEAD>";
  if(IsPrinting() == 1)
    echo '<BODY ONLOAD="javascript:print()">';
  else
    echo '<BODY>';
}
else
{
  $hdrs .= '<link rel=stylesheet href="css" type="text/css">';

  Headers($pagetitle);

  echo '<h1>',
    ereg_replace('(Bisqwit)', '<a href="http://\1.iki.fi/">\1</a>', $pagetitle),
       '</h1>';

  NavBar('');
}
print $page;

if(IsPrinting() > 0)
{
  print '</BODY></HTML>';
}
else
{
  Epilogue();
  Footers();
}

if($outset != 'utf-8')
{
  $s = ob_get_contents();
  ob_end_clean();
  
  if($outset == 'euc-jp')
  {
    include 'shodouka.php';
  }
  passthru('echo '.shellfix($s).'|recode utf8..'.shellfix($outset).' 2>&1');
  
  if($outset == 'euc-jp')
  {
    ShodoukaEnd();
  }
}

GzCompressEnd();
