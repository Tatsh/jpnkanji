<?

/* This file is maintained in ~/src/japkanji/ - do not modify otherwhere */

if($dump)header('Content-type: text/html; charset='.$dump);
include '/WWW/cache.php';
cache_init(0);
if($dump=='euc-jp' || $dump=='euc_jp')cache_disablecompression();

#$omitmenu=1;
include '/WWW/headers.php';
include_once 'deconjfun.php';
include_once 'kanjisqlfun.php';

$hdrs .= '<meta name="robots" content="nofollow">';

$stylext .= 'TH.jpoz{background:#CCCCCC}'.
            'TD.jpdat{background:#EEEEEE}'.
            'SPAN.debug{color:#60C0EE;font-size:80%}'.
            'SPAN.result{font-size:125%}'.
            'SPAN.info{font-size:75%}';

# Returns 1, 2 or 3, telling which class this verb is.
function verbcategory($sana)
{
  // group 3: s-uru, k-uru
  // group 2: tabe-ru, ne-ru, ochi-ru, mi-ru
  // group 1: yom-u, tob-u, shin-u, ara(w)-u, ur(u), tats(u), oyog(u), kik(u), hanas(u)
  // group 1: shir-u, hashir-u, hai-ru, kaer-u, suber-u, shaber-u
  
  for($len = 3; $len >= 1; $len--)
    if(ereg(substr('uru', 3-$len).'$', $sana))
      if($len != 2 || ereg('[ei]ru$', $sana))
      {
        if($len != 3 || ereg('[sk]uru$', $sana))
          break;
      }
  
  if($len == 2) /* -ru */
  {
    $last = ereg_replace('.* ', '', $sana);
    /* Check for exceptions (group 1 verbs that end with -eru or -iru) */
    
    /*
 Exceptions to the general verb classifications
 (source: http://www.geom.uiuc.edu/~burchard/nihongo/notes.html )

   +---------------------------------------------------+
   |       Class-[I]       |        Class-[II]         |
   |-----------------------+---------------------------|
   | kaeru (``to change'') | kaeru (``to return'')     |
   |-----------------------+---------------------------|
   | iru (``to be'')       | iru (``to be necessary'') |
   |-----------------------+---------------------------|
   | kiru (``to wear'')    | kiru (``to cut'')         |
   |-----------------------+---------------------------|
   |                       | shiru; hairu; hashiru     |
   +---------------------------------------------------+
    */
    
    if($last=='siru'   || $last=='hasiru'
    || $last=='hairu'  || $last=='kaeru'
    || $last=='suberu' || $last=='shaberu'
    || $last=='iru')
      return 1;
  }

  return $len;
}

# Returns the stem of the verb; examples: hanasu->hanas, taberu->taber, suru->s
function verbbody($sana)
{
  $category = verbcategory($sana);
  $rest = substr($sana, 0, strlen($sana) - $category);
  
  if($category == 1 && ereg('[aiueo]$', $rest)) $rest .= 'w';
  return $rest;
}

# Returns the stem of the adjective; examples: nai->na, oishii->oishi
function adjbody($sana)
{
  if(ereg('i$', $sana))return substr($sana, 0, strlen($sana)-1);
  return $sana;
}

# Extends the verb.
function joinverb($sana, $liite)
{
  $body = verbbody($sana);
  $last = ereg_replace('.* ', '', $body);
  $first = substr($body, 0, strlen($body)-strlen($last));
  
  switch($liite[0])
  {
    case 't': // ta, te
    
      if($last == 'ik')         return $first.'it'.$liite; // iku -> itte, not iite
      if(ereg('[trw]$', $last)) return substr($body, 0, strlen($body)-1) . 't'  . $liite;
      if(ereg(    'k$', $last)) return substr($body, 0, strlen($body)-1) . 'ki'  . $liite;
      if(ereg(    's$', $last)) return substr($body, 0, strlen($body)-1) . 'si' . $liite;
      if(ereg('[mnb]$', $last)) return substr($body, 0, strlen($body)-1) . 'nd' . substr($liite, 1);
      if(ereg(    'g$', $last)) return substr($body, 0, strlen($body)-1) . 'id' . substr($liite, 1);
      return $body . $liite;
    
    case 'n': // nai   (negative)
    case 'r': // rarei (passive)
    case 's': // sasei (causative/permissive)
              // sarei (passive causative)
    
      if($last == 'ar')  return $first.$liite;       // aru -> nai
      if($last == 'ir')  return $first.'i'.$liite;   // iru -> inai
      if($last == 'des') return $first.'de '.$liite; // desu -> de nai
      if($last == 'k')   return $first.'ko'.$liite;  // kuru -> konai
      if($last == 's')   return $first.'si'.$liite;  // suru -> sinai
      
      if(ereg('[aiueo]$', $last))return $body . $liite;
      
      /* w+a -> wa */
      
      return $body . 'a' . $liite;
    
    case 'i': // imasu (polite)
              // itai  (desiderative)
              // i     (imperative)
    
      if($last == 'ir') return $first.$liite; // iru -> imasu/itai
      if(ereg('[aiueo]$', $last))return $body . substr($liite, 1);
      
      if($last == 'kudasar'
      || $last == 'nasar'
      || $last == 'gozar'
      || $last == 'irassyar'
      || $last == 'ossyar')
      {
        // The honorary verbs kudasaru, nasaru, gozaru, irassharu, 
        // ossharu drop the r in their Imperative forms kudasai
        // (etc.) and the Polite forms kudasaimas- (etc.).
        
        return substr($body, 0, strlen($body)-1) . $liite;
      }
      
      /* w+i -> i */
      
      if(ereg('w$', $last)) return substr($body, 0, strlen($body)-1) . $liite;
      
      return $body . $liite;
      
      /* Would: w+o -> yo */
  }
  
  return $body . $liite;
}

# Turns the basic form of adjective or verb to past-form.
function makepast($s)
{
  if(ereg('i$', $s))return adjbody($s) . 'katta';
  return joinverb($s, 'ta');
}

# Turns the verb to progressive.
function progressiiviksi($s)
{
  return joinverb($s, 'te');
}

# Makes the action wanted. (Returns an adjective)
function makedesirative($s)
{
  return joinverb($s, 'itai');
}

# Turns the verb into conditional.
function konditionaaliksi($s)
{
  return joinverb($s, 'taru');
}

function konditionaali_pos_tara($s)
{
  $first = makepast($s);
  return $first . 'ra';
  // e.g., iku -> itta -> ittara
}

function konditionaali_pos_eba($s)
{
  $stem =  substr($s, 0, strlen($s)-1);
  return $stem . 'eba';
  // works also for the irregular ones: kuru -> kureba, suru -> sureba
}

function konditionaali_neg_tara($s)
{
  $stmp = $s . ' desu';
  $stmp = negaksi($s);
  return makepast($stmp) . 'ra';
}

function konditionaali_neg_eba($s)
{
  $first = negaksi($s);
  $stem = substr($first, 0, strlen($first)-1);
  return $stem . 'kereba';
  // e.g., suru -> shinai -> shinakereba
}



# Muuttaa verbin perus- tai kohteliaan muodon negatiiviseksi.
# Adjektiivin perusmuoto muutetaan negatiiviseksi lis‰‰m‰ll‰
# siihen aru ja taivuttamalla se mahdollisesti ensin kohteliaaseen
# muotoon, ja sen j‰lkeen negatiiviseksi.
function negaksi($s)
{
  if(ereg('masu$', $s))return ereg_replace('masu$', 'masen', $s);
  return joinverb($s, 'nai');
}

# Makes the basic form of a verb to be polite.
function polite($s)
{
  return joinverb($s, 'imasu');
}

# Modifies an adjective to look like a verb.
# Don't use this before bending to past form (it would make -ita, not -katta),
# but only when you want to add the aru-verb after this.
function verbiksi($s)
{
  return adjbody($s) . 'ku';
}

$functions = array
(
  /* Intermediate */
  '1' => array('pass' => 'rare:1',
               'caus' => 'sase:1',
               'pasc' => 'sare:1',
               'prog' => 'te i:1',
               'masu' => 'mas:2',
               'nega' => 'na:A',
               ''
              )
);



headers('Japanese verb conjugator');
echo '<h1>Japanese verb conjugator</h1>';
print '<center><small>Shows the inflections of a verb or an adjective.</small></center>';

NavBar('');

if(!isset($s))$s = 'miru';

?>
<form method=GET action="japverb">

Verb or adjective: <input type=text name=s value="<?=htmlspecialchars($s)?>" size=20>
(in its basic form)<br>
Hiragana encoding: 
 <select name=dump>
  <option value=""<?            if($dump=='')           echo ' selected'; ?>>unihtml</option>
  <option value="iso-2022-jp"<? if($dump=='iso-2022-jp')echo ' selected'; ?>>iso-2022-jp</option>
  <option value="shift_jis"<?   if($dump=='shift_jis')  echo ' selected'; ?>>sjis</option>
  <option value="euc-jp"<?      if($dump=='euc-jp')     echo ' selected'; ?>>EUC-JP</option>
 </select>
 (select the one supported by your browser)<br>
 
<p>
<input type=submit value="Conjugate"> (bends the word)
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
  
  $res = 'ß &nbsp; <em>';
  
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
    preg_replace('/ß/e', 'kanapad($output[$index],$hiratab[$index])',
      $data
    );
}

function DoAdjective($s)
{
  $table = array();
  foreach(array(0,1) as $nega)
    foreach(array(0,1,2) as $polite)
    {
      /* Positiivisella ei ole kuin yksi kohtelias muoto. */
      if($polite==2 && !$nega)continue;
      
      foreach(array('', 'imp') as $aika)
      {
        $stmp = $s;
        
        $line = '';
        
        /* ALGORITMI:
          
         Jos on negatiivinen,
           - muutetaan verbinkaltaiseksi (-i -> -ku)
           - lis‰t‰‰n aru-verbi
           - Jos kohtelias, muutetaan aru kohteliaaksi (arimasu)
           - muutetaan negatiiviseksi (nai tai arimasen)
         Jos on imperfekti,
           - jos on muodollinen kohteliaisuus, lis‰t‰‰n desu (koska arimasen ei en‰‰ taivu)
           - taivutetaan imperfektiin (ku/i -> katta, desu -> deshita)
         Jos on puhekielinen kohtelias,
           - lis‰t‰‰n desu.
        
        */
        
        if($nega)
        {
          $stmp = verbiksi($s);
          $stmp .= ' aru';
          if($polite == 2)$stmp = polite($stmp); 
          $stmp = negaksi($stmp); /* Form=arimasen, coll=nai */
        }
        
        if($aika == 'imp')
        {
          if($polite == 2) $stmp .= ' desu';
          $stmp = makepast($stmp);
        }
        
        if($polite == 1) $stmp .= ' desu';
        
        $line = '<span class=debug>'.$line.'</span> <b><span class=result>'.
                debugprint($stmp).'</span></b>';

        $table[$polite][$nega][$aika] = $line;
      }
    }

  /*
                             CURRENT           PAST
    PLAIN POS             shiroi             shirokatta
    POLITE POS            shiroi desu        shirokatta desu
    PLAIN NEGA            shiroku nai        shiroku nakatta
    POLITE NEGA COLL      shiroku nai desu   shiroku nakatta desu
    POLITE NEGA FORM      shiroku arimasen   shiroku arimasen deshita
  
2119Warp= Ooks‰ siell‰?
2120=Warp joo.
2120Warp= na-adjektiiveja ovat esimerkiksi benri (convenient), fuben (inconvenient), shizuka (quiet, peaceful), kiree (clean)
2120=Warp Miss‰ kohtaa noissa on "na"?
2121Warp= Se tulee niiden loppuun joissain tapauksissa (en tied‰ milloin).
2123=Warp No tuosta ei paljoa ollut apua :-/
2124Warp= Kohtelias muoto -> perusmuoto -muunnokset olisi niille esim: fuben desu -> fuben da, fuben dewa arimasen -> fuben
+         dewanai, fuben deshita -> fuben datta, fuben dewa arimasendeshita -> fuben dewanakatta
2125Warp= i-verbien kanssa sitten: ookii desu -> ookii, ookii deshita -> ookikatta
2126Warp= Sitten negatiiviset muodot muistaakseni ookinai ja ookinakatta (tosin m‰ en nyt lˆyd‰ noita n‰ist‰ papruista).
2127=Warp No tuosta oli jo jotain apua
2130=Warp Osaatko taivuttaa tuota benri‰ samalla tavalla?
2131Warp= Se menee ihan samalla tavalla. Benri da, benri dewanai, benri datta, benri dewanakatta.


  */


  echo '<table border=><tr><th></th>';
  echo '<th class=jpoz>Present</th>',
       '<th class=jpoz>Past</th>';
  echo '</tr>';
        
  dohiragana();
  foreach(array(0,1) as $nega)
    foreach(array(0,1,2) as $polite)
    {
      if($polite==2 && !$nega)continue;
      
      echo '<tr><th class=jpoz>';
      switch($polite * 2 + $nega)
      {
        case 0: print 'Plain positive'; break;
        case 1: print 'Plain negative'; break;
        case 2: print 'Polite positive'; break; /* coll syntax */
        case 3: print 'Polite negative colloquial'; break;
        case 5: print 'Polite negative formal'; break;
      }
      echo '</th>';

      foreach(array('', 'imp') as $aika)
      {
        echo
          "<td class=jpdat>\n",
          hoida($table[$polite][$nega][$aika]),
          "</td>\n";
      }
      
      echo '</tr>';
    }
  
  echo '</table>';
}

function DoVerb($s)
{
  $table = array();
  foreach(array(0,1) as $nega)
    foreach(array(0,1,2) as $polite)
    {
      /* Positiivisella ei ole kuin yksi kohtelias muoto. */
      if($polite==1 && !$nega)continue;
      foreach(array('pre', 'pro', 'imp') as $aika)
      {
        $stmp = $s;
        $line = '';
        
        /* ALGORITMI:
        
         Jos progressiivi,
           - lis‰t‰‰n per‰‰n iru-verbi.
         Jos muodollinen kohtelias,
           - muutetaan kohteliaaksi (-masu)
         Jos negatiivinen,
           - muutetaan negatiiviseksi (-nai tai -masen)
         Jos imperfekti,
           - jos on muodollinen kohteliaisuus ja negatiivinen,
             lis‰t‰‰n desu (koska -imasen ei en‰‰ taivu)
           - taivutetaan imperfektiin (-ta)
         Jos on puhekielinen kohtelias,
           - lis‰t‰‰n desu.
        
        */
        
        if($aika == 'pro')
        {
          $stmp = progressiiviksi($stmp);
          $stmp .= ' iru';
        }
        
        if($polite == 2)  $stmp = polite($stmp);
        if($nega)         $stmp = negaksi($stmp);
        
        if($aika == 'imp')
        {
          if($nega && $polite == 2)
            $stmp .= ' desu';
          $stmp = makepast($stmp);
        }
        
        if($polite == 1)
        {
          if($stmp == 'de nai')
            $stmp = negaksi(polite('dewa aru'));
          else
            $stmp .= ' desu';
        }
        
        $line = '<span class=debug>'.$line.'</span> <b><span class=result>'.
                debugprint($stmp).'</span></b>';

        $table[$polite][$nega][$aika] = $line;
      }
    }
  
  /*
                             CURRENT         PROGRESSIVE           PAST
    PLAIN POS             hanasu           hanashite iru        hanashita
    POLITE POS            hanashimasu      hanashite imasu      hanashimashita
    PLAIN NEGA            hanasanai        hanashite inai       hanasanakatta
    POLITE NEGA COLL      hanasanai desu   hanashite inai desu  hanasanakatta desu
    POLITE NEGA FORM      hanashimasen     hanashite imasen     hanasu arimasen deshita
  
  */

  echo '<table border=><tr><th></th>';
  
  echo '<th class=jpoz>Present / attibutive</th>',
       '<th class=jpoz>Progressive (ongoing)</th>',
       '<th class=jpoz>Past / present perfect</th>';
  
  echo '</tr>';
  
  dohiragana();
  foreach(array(0,1) as $nega)
    foreach(array(0,1,2) as $polite)
    {
      if($polite==1 && !$nega)continue;
      
      echo '<tr><th class=jpoz>';
      switch($polite * 3 + $nega)
      {
        case 0*3+0: print 'Plain positive'; break;
        case 0*3+1: print 'Plain negative'; break;
        case 2*3+0: print 'Polite positive'; break; /* form syntax */
        case 1*3+1: print 'Polite negative colloquial'; break;
        case 2*3+1: print 'Polite negative formal'; break;
      }
      echo '</th>';

      foreach(array('pre', 'pro', 'imp') as $aika)
      {
        echo
          "<td class=jpdat>\n",
          hoida($table[$polite][$nega][$aika]),
          "</td>\n";
      }
      
      echo '</tr>';
    }
  
  echo '</table>';
}

function DoConditional($s)
{
  $table = array();
  
  echo '<p><b>Conditional forms: (I) - plain, (II) - provisional, plain</b><p>';

  echo '<table border=><tr><th></th>';
  echo '<th class=jpoz>Positive</th>',
       '<th class=jpoz>Negative</th>';
  echo '</tr>';

  foreach(array('I','II') as $cond)
  { 
  
    echo '<tr><th class=jpoz>Conditional ('.$cond.')</th>';
    foreach(array(1,0) as $nega)
    {
      if($cond == 'I' && $nega) $stmp = konditionaali_pos_tara($s);
      if($cond == 'I' && !$nega) $stmp = konditionaali_neg_tara($s);
      if($cond == 'II' && $nega) $stmp = konditionaali_pos_eba($s);
      if($cond == 'II' && !$nega) $stmp = konditionaali_neg_eba($s);
    
      $line = '<td class=jpdat></span> <b><span class=result>'.
                debugprint($stmp).'</span></b></td>';     
      echo hoida($line);
      if(!$nega) echo '</tr>';
    }
  }
  echo '</table>';
}

/* Make sure the expression only contains valid syllables. */
$s = str_replace('˚', 'uu',
     str_replace('Í', 'ee',
     str_replace('Ó', 'ii',
     str_replace('‚', 'aa',
     str_replace('Ù', 'ou', $s)))));

$s = UnTsu($s);

$s = str_replace(array('ji', 'jy', 'j',  'c', 'th', 'tw'),
                 array('zi', 'zy', 'zy', 't', 'ty', 'ty'),
                       $s);

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

if($s)
{
  if(eregi('[aeiou]i$', $s))
  {
    /* adjective */
    $hiragana = '';
    DoAdjective($s);
  }
  elseif(ereg('u$', $s))
  {
    if(ereg('.*imasu$', $s))
    {
      print 'You say this is the basic form of the verb? Sure does look like a polite positive present form.';
      print '<br>You should try "';$bar=ereg_replace('imasu$', 'u', $s);
      $hiragana='';$foo = debugprint($bar);
      dohiragana();echo '<a href="'.verblink($bar).'">',hoida($foo),'</a>';
      if($s=='simasu')
      {
        print '", "';$bar=Tsu('suru');
        $hiragana='';$foo = debugprint($bar);
        dohiragana();echo '<a href="'.verblink($bar).'">',hoida($foo),'</a>';
      }
      if($s=='kimasu')
      {
        print '", "';$bar=Tsu('kuru');
        $hiragana='';$foo = debugprint($bar);
        dohiragana();echo '<a href="'.verblink($bar).'">',hoida($foo),'</a>';
      }
      print '" or "';$bar=Tsu(ereg_replace('imasu$', 'iru', $s));
      $hiragana='';$foo = debugprint($bar);
      dohiragana();echo '<a href="'.verblink($bar).'">',hoida($foo),'</a>';
      print '" instead.<p>';
    }
    
    echo 'Verb category: ',
         str_repeat('I', verbcategory($s)),
         ' (radical: "';
    $bar=Tsu($s);
    $hiragana='';$foo = debugprint($bar);
    dohiragana();echo hoida($foo), '")';

    echo '<p>';
    
    $hiragana = '';
    DoVerb($s);
    DoConditional($s);
    
    print '<p><b>An adjective formed from the verb, giving an [un]desired action:</b><br>';
    $hiragana = '';
    DoAdjective(makedesirative($s));
/*
    print '<p>If you want conditional, try conjugating ';
    $hiragana='';
    $bar = konditionaaliksi($s);
    $foo = debugprint($bar);
    dohiragana();echo '<a href="verb=',rawurlencode($bar),'">',hoida($foo),'</a>';
    print '.<br>';
*/
  }
  else
  {
    echo 'The word must be an adjective or a verb in the basic form. ',
         'Are you sure it is? ',
         'Try to <a href="http://kanjidict.stc.cx/japverb3.php?s=',
             urlencode($s), '">deconjugate it</a>.';
  }
}

?>
<p><small>Written by Bisqwit (<a href="http://bisqwit.iki.fi/">http://bisqwit.iki.fi/</a>)
<br>and Aarno Hohti
</small>

<p>See also: <a href="http://www.geom.uiuc.edu/~burchard/nihongo/">Paul Burchard's verb tables</a>
<?
Epilogue();

?>
Disclaimer: The accuracy of the operation of this conjugator is
strictly limited to the progress of my Japanese studies.

<?
Footers();
cache_end();
