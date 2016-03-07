<?php

/* This file is maintained in ~/src/japkanji/ - do not modify otherwhere */

if($dump)header('Content-type: text/html; charset='.$dump);
include '/WWW/cache.php';
cache_init(0);

#$omitmenu=1;
include '/WWW/headers.php';

$stylext .= 'TH.jpoz{background:#CCCCCC}'.
            'TD.jpdat{background:#EEEEEE}'.
            'SPAN.debug{color:#60C0EE;font-size:80%}'.
            'SPAN.result{font-size:125%}'.
            'SPAN.info{font-size:75%}'.
            'SPAN.fail{color:#A00070}';

require 'deconjfun.php';
require 'kanjisqlfun.php';

headers('Japanese verb deconjugator');
echo '<h1>Japanese verb deconjugator</h1>';
print '<center><small>Traces back the inflections of a verb or an adjective.</small></center>';

NavBar('');

if(!isset($s))$s = 'oishiku nakatta desu';

?>
<form method=GET action="japverb3">

Verb or adjective: <input type=text name=s value="<?=htmlspecialchars($s)?>" size=20>
(in any form)<br>
Hiragana encoding: 
 <select name=dump>
  <option value=""<?            if($dump=='')           echo ' selected'; ?>>unihtml</option>
  <option value="iso-2022-jp"<? if($dump=='iso-2022-jp')echo ' selected'; ?>>iso-2022-jp</option>
  <option value="shift_jis"<?   if($dump=='shift_jis')  echo ' selected'; ?>>sjis</option>
  <option value="euc_jp"<?      if($dump=='euc_jp')     echo ' selected'; ?>>EUC-JP</option>
 </select>
 (select the one supported by your browser)<br>
 <input type=checkbox name=nofull <?if($nofull)echo ' checked';?>> (skip some guesses)<br>
 <input type=checkbox name=fails <?if($fails)echo ' checked';?>> (include obvious fails)<br>
 <input type=checkbox name=nodict <?if($nodict)echo ' checked';?>> (ignore dictionary)<br>
 
<p>
<input type=submit value="Deconjugate"> (deconjugates the word)
</form>
<p>
<?
if(!$dump)$dump = 'unihtml';

$hiragana = '';

function kana2html($s, $fix=1)
{
  if($fix)
    $s = Tsu($s);

  return /*
    str_replace('ou', '&ocirc;',
    str_replace('uu', '&ucirc;',
    str_replace('aa', '&acirc;',
    str_replace('ii', '&icirc;',
    str_replace('ee', '&ecirc;', */
     htmlspecialchars($s)/*)))))*/;
}
function debugprint($s, $desc='')
{
  global $hiragana;

  $s = Tsu($s);
  
  $hiragana .= str_replace(' ', '', $s)."\n";
  
  $res = '§ &nbsp; <em>';
  
  $res .= kana2html($s,0);
  if($desc)$res .= ' (<span class=info>'.$desc.'</span>)';
  $res .= '</em>';
  
  return $res;
}
function kanapad($chira, $hira)
{
  global $index;
  
  $index++;  
  return $chira;
}
function dohiragana()
{
  global $hiragana, $hiratab;
  $hiratab = explode("\n", $hiragana);
 
  global $output, $dump;
  $output = array();
  exec(
    'echo '.shellfix(strtolower($hiragana)).
    '|sed -f /root/src/kr2k.sed|/usr/local/bin/charconv shift_jis '.shellfix($dump),
    $output);
  
  global $index;
  $index = 0;
}

function hoida($data, $maxtavu=0)
{
  global $hiratab, $output, $index;
  return
    preg_replace('/§/e', 'kanapad($output[$index],$hiratab[$index])',
      $data
    );
}

/* Make sure the expression only contains valid syllables. */
$s = UnCircumflex($s);
$s = UnTsu($s);
$s = UnWeird($s);

$snew = tavumake(tavubreak($s));
$lendiff = strlen(kana2html($s)) - strlen(kana2html($snew));
if($lendiff)
{
  echo 'Warning: Length of input string truncated by ', $lendiff,
       ' before parsing; parsing "';
  $foo = debugprint(kana2html($snew));
  dohiragana();
  print hoida($foo);
  echo '" instead.<p>';
}
$s = $snew;

$ilev=0;
function Deeper() { global $ilev;$ilev++; }
function Undeeper() { global $ilev;$ilev--; }
function IndStuff() { global $ilev;return str_repeat(' |', $ilev); }
function DumpAnalysis($analysis)
{
  global $functions;
  $int  = &$functions['int'];
  $term = &$functions['term'];
  $misc = &$functions['misc'];
  
  Deeper();
  ob_start();
  
  #echo IndStuff(), 'Analyzing "', $analysis['input'], '" (', $analysis['goal'], ')...<br>';
  
  $results = &$analysis['results'];
  if(!is_array($results))
  {
  	$results = array();
  }
  $most=0; foreach($results as $tab) if($tab['score'] > $most) $most = $tab['score'];
  
  foreach($results as $tab)
  {
    $scoretext = '';
    if($tab['score'] >= $most)
      $scoretext .= IndStuff() . '->';
    else
      $scoretext .= IndStuff() . '-->';
      
    $seemslike = $misc[$tab['class']];
    
    $scoretext .= 'Score '. $tab['score']
                 .': "'. $tab['input']. '" seems like '. $seemslike
                 .', ';
    if($int[$tab['key']])
    {
      $scoretext .= $int[$tab['key']];
      $scoretext .= $tab['goal'] ? ' part' : ' (<em>incomplete</em>)';
    }
    else
      $scoretext .= $term[$tab['key']];
    
    $radicals = $tab['radicals'];
    if(!is_array($radicals))
      $radicals = array();

    $k = array(); foreach($radicals as $radical => $dummy) $k[$radical] = $radical;
    if(count($k) > 1)
    {
      $scoretext .= '; radical choices: ';
      foreach($k as $choice)$scoretext .= " ($choice)";
      $scoretext .= '<br>';
    }
    else
    {
      $scoretext .= '; radical="';
      foreach($k as $choice)$scoretext .= $choice;
      $scoretext .= '"<br>';
    }
    
    if(is_array($tab['subtries']))
    {
      ob_start();
      DumpAnalysis($tab['subtries']);
      $subresult = ob_get_contents();
      ob_end_clean();
      if(strlen($subresult))
      {
        echo $scoretext, $subresult;
      }
    }
    
    foreach($radicals as $radical => $result)
    {
      $k = $result['input'];
      //echo'<pre style="color:blue">';print_r($result);echo'</pre>';
      
      switch($result['failcode'])
      {
        case 'garbage':
          echo $scoretext; $scoretext='';
          echo IndStuff(), '<span class=fail>Failed. "',
                           $result['input'], '" is garbage.</span><br>';
          break;

        case 'nodict':
          echo $scoretext;$scoretext='';
          echo Indstuff(), '<span class=fail>Failed. "',
               debugprint($k), '" was not found in dictionary.</span><br>';
          break;
        
        case 'noadj':
          echo $scoretext;$scoretext='';
          echo Indstuff(), '<span class=fail>Failed. "',
               debugprint($k), '" is not ', $seemslike,
               ' (-i should be a syllable).</span><br>';
          break;
        
        case 'dictmismatch':
          echo $scoretext;$scoretext='';
          echo Indstuff(), '<span class=fail>Failed. By dictionary, "',
               debugprint($k), '" is not ', $seemslike, '.</span><br>';
          break;
        
        default:
          $dict_id = $result['dicts'];

          $tmp = 'http://kanjidict.stc.cx/?btnJ=1&exact=1';
          foreach($dict_id as $tmp2)$tmp .= '&d'.$tmp2.'=1';
          $tmp .= '&s='.urlencode(tsu($k));

          $desc = '<a href="'.htmlspecialchars($tmp).'">lookup</a>';
          $desc .= '/<a href="verb='.htmlspecialchars(urlencode(tsu($k))).'">conjugate</a>';
          
          echo $scoretext;$scoretext='';
          echo IndStuff(), '---> Basic ', $seemslike, ' form could be ',
               debugprint($k, $desc), '.<br>';
          break;
      }
    }
  }

  UnDeeper();
  
  $output = ob_get_contents();
  ob_end_clean();
  
  if(strlen($output))
    print $output;
}

if($s)
{
  ob_start();
  
  $analysis = DeconjAnalyze(str_replace(' ', '', $s));
  #echo'<pre>';print_r($analysis);echo'</pre>';
  
  DumpAnalysis($analysis);
  
  $result = ob_get_contents();
  ob_end_clean();
  if(!strlen($result))
    $result = 'Sorry, nothing mentionable for "'. debugprint($s).
              '".<br>If it something, it probably is a noun or a particle.<br>
              Try <a href="find='.tsu($s).'">dictionary</a>?';
  dohiragana();
  print hoida($result);
}

?>
<p><small>Written by Bisqwit (<a href="http://bisqwit.iki.fi/">http://bisqwit.iki.fi/</a>)</small>
<p>See also: <a href="http://www.geom.umn.edu/~burchard/nihongo/">Paul Burchard's verb tables</a>

<?
Epilogue();

?>
Disclaimer: The accuracy of the operation of this deconjugator is
strictly limited to the progress of my Japanese studies.

<?
Footers();
cache_end();
