<?php
/* This file is maintained in ~/src/japkanji/ - do not modify otherwhere */

function UnTsu($s)
{
  /* Transforms romaji to syllables */
  return str_replace(
            array('chi', 'shi', 'ch', 'sh', 'tsu', 'f'),
            array('ti',  'si',  'ty', 'sy', 'tu',  'h'),
               $s);
}

function Tsu($s)
{
  /* Transforms syllables to romaji */
  return str_replace(
            array('ti',  'si',  'ty', 'sy', 'tu',  'hu', 'sfu'),
            array('chi', 'shi', 'ch', 'sh', 'tsu', 'fu', 'shu'),
               $s);
}

function UnCircumflex($s)
{
  return
    str_replace('û', 'uu',
    str_replace('ê', 'ee',
    str_replace('î', 'ii',
    str_replace('â', 'aa',
    str_replace('ô', 'ou', $s)))));
}

function UnWeird($s)
{
  return
    str_replace(array('ji', 'jy', 'j',  'c', 'th', 'tw'),
                array('zi', 'zy', 'zy', 't', 'ty', 'ty'),
                      $s);
}

# Helper function for tavubreak()
function tavuadd($s)
{
  global $tavulist; $s = str_replace('\"','"',$s); $t = $s;
  $sp = (!count($tavulist) || !ereg('[a-z]',$tavulist[count($tavulist)-1]));
  if($sp) { if($s[0]==$s[1]) $s = substr($s,1); if(!strlen(ereg_replace('[^a-z]','',$s)))$s=''; }
  if(strlen($s)) $tavulist[] = $s; return substr($t, 0, strlen($t)-strlen($s));
}

# Returns an array containing the syllables of the clause.
function tavubreak($s)
{
  global $tavulist;
  $tavulist = array();
  preg_replace(
    '/((kk?|gg?|ss?|zz?|tt?|dd?|nn?|hh?|bb?|pp?|mm?|rr?)?(y[auo]|[aiueo])|w[ao]|n|[ .,!?])/e',
    "tavuadd('\\1')",
    trim($s));
  return $tavulist;
}

# Builds a clause from the list of syllables.
function tavumake($array)
{
  $result = '';
  foreach($array as $tavu) $result .= $tavu;
  #print'<pre>';print_r($array);print'</pre>';
  return trim($result);
}

function mask_on($ending, $body)
{
  $last = ereg_replace('.* ', '', $body);
  $first = substr($body, 0, strlen($body)-strlen($last));
  
  switch($ending[0])
  {
    case '^':
      return $first . substr($ending, 1);
    
    case 't': // ta, te
    
      if($last == 'ik')         return $first.'it'.$ending; // iku -> itte, not iite
      if(ereg('[aiueo][auo]$', $last)) return $body . 't' . $ending;
      if(ereg(       '[mnb]$', $last)) return substr($body, 0, strlen($body)-1) . 'nd' . substr($ending, 1);
      if(ereg(           'g$', $last)) return substr($body, 0, strlen($body)-1) . 'id' . substr($ending, 1);
      if(ereg(           'k$', $last)) return substr($body, 0, strlen($body)-1) . 'i'  . $ending;
      if(ereg(       '[ei]r$', $last)) return substr($body, 0, strlen($body)-1) . $ending;
      if(ereg(           'r$', $last)) return substr($body, 0, strlen($body)-1) . 't'  . $ending;
      if(ereg(           's$', $last)) return substr($body, 0, strlen($body)-1) . 'si' . $ending;
      
      break;
    
    case 'o': // ou
    
      if(ereg('(mas|[aiueo])$', $last))
        return $body . 'y' . $ending;
      
      break;
    
    case 'e': // e (pot), eba (cond)

      if(ereg('mas$', $last))return $body . 'ur' . $ending;       // masu+eba -> masureba
      
      break;
    
    case 'a': // anai
    
      if(ereg('[uie]$', $last)) 
      return substr($body, 0, strlen($body)-1) . $ending;
      break;
      
    case 'n': // na
    
      if($ending == 'nai')
      {
        if($last == 'ar')  return $first.$ending;       // aru -> nai
        if($last == 'ir')  return $first.'i'.$ending;   // iru -> inai
      }
      
      break;
    
    case 'm': // mas (sarjan 2 verbeille)
      if($ending != 'mas')break;
      return $body . $ending;
      
    case 'i': // imas (sarjan 1 verbeille)
      if($ending == 'imas' && $last == 'ir') 
        return $first . $ending;

      if($last == 'kudasar'
      || $last == 'nasar'
      || $last == 'gozar'
      || $last == 'irassyar'
      || $last == 'ossyar')
      {
        // The honorary verbs kudasaru, nasaru, gozaru, irassharu, 
        // ossharu drop the r in their Imperative forms kudasai
        // (etc.) and the Polite forms kudasaimas- (etc.).
        
        return substr($body, 0, strlen($body)-1) . $ending;
      }
      
      break;
      
  }
  
  if(ereg('[aiueo]$', $last))
  {
    if($ending[0]=='a') $ending = 'w' . $ending;
    if($ending[0]=='o') $ending = 'y' . $ending;
  }
  
  return $body . $ending;
}

function mask_off($ending, $word)
{
  $last = ereg_replace('.* (.)', '\1', $word);
  $first = substr($word, 0, strlen($word)-strlen($last));
  switch($ending[0])
  {
    case '^':
      return substr($word, 0, strlen($word)-strlen($ending)+1);
    
    case 't': // ta, te
      
      // itta -> iku */
      if($last == 'it' . $ending) return $first . 'ik';
      
      /* nda, nde -> mu, nu, bu */
      if(ereg('nd'.substr($ending,1).'$', $last))
      {
        $b = substr($word, 0, strlen($word) - strlen($ending)-1);
        return array($b.'m', $b.'n', $b.'b');
      }
      #print "first='$first' last='$last' ending='$ending'<br>";
      
      # ida, ide -> gu */
      if(ereg('id'.substr($ending,1).'$', $last))
        return substr($word, 0, strlen($word) - strlen($ending)-1) . 'g';
      
      /* sita, site -> su */
      if(ereg('si'.$ending.'$', $last))
        return substr($word, 0, strlen($word) - strlen($ending)-1);
      
      /* ita, ite -> ku */
      if(ereg('i'.$ending.'$', $last))
        return substr($word, 0, strlen($word) - strlen($ending)-1) . 'k';
      
      /* atta,itta,utta,etta,otta, atte,itte,utte,ette,otte -> _r */
      if(ereg('[aiueo]t'.$ending.'$', $last))
      {
        return substr($word, 0, strlen($word) - strlen($ending)-1) . 'r';
      }
      
      /* yatta, yatte -> yar, ya, yi, yu, ye, yo */
      if(ereg('t'.$ending.'$', $last))
      {
        $b = substr($word, 0, strlen($word) - strlen($ending)-1);
        return array($b.'r', $b.'a', $b.'i', $b.'u', $b.'e', $b.'o');
      }
      
      /* ta,te -> r */
      if(ereg($ending.'$', $last))
      {
        $b = substr($word, 0, strlen($word) - strlen($ending));
        return array($b . 'r', $b);
      }
      
      break;
    
    case 'o': // ou
    
      if(ereg('masy'.$ending.'$', $last))
        return substr($word, 0, strlen($word) - strlen($ending)-1);
      if(ereg('[aiueo]y'.$ending.'$', $last))
        return substr($word, 0, strlen($word) - strlen($ending)-1);
      
      break;
    
    case 'e': // e (pot), eba (cond)

      if(ereg('masur'.$ending.'$', $last))
        return substr($word, 0, strlen($word) - strlen($ending)-2);
      
      break;
    
    case 'a': // anai
    
      return substr($word, 0, strlen($word) - strlen($ending));
    
    case 'n': // na
    
      if($last == 'nai') return $first . 'aru';
      if($last == 'inai') return $first . 'iru';
      
      break;
    
    case 'i': // imas (sarjan 1 verbeille)
      if($last == 'i' . $ending)
        return $first . 'ir';
      
      #print 'last="'.$last.'",ending="'.$ending.'"';
      return substr($word, 0, strlen($word)-strlen($ending));

    case 'm': // mas (sarjan 2 verbeille)
      if($ending != 'mas')break;

      if($last == 'kudasai'.$ending
      || $last == 'nasai'.$ending
      || $last == 'gozai'.$ending
      || $last == 'irassyai'.$ending
      || $last == 'ossyai'.$ending)
      {
        return substr($word, 0, strlen($word)-strlen($ending)-1) . 'r';
      }
      
      return substr($word, 0, strlen($word)-strlen($ending));
  }
  
  if($ending[0]=='a' && ereg('[aiueo]w'.$ending.'$', $last))
  {
    return substr($word, 0, strlen($word)-strlen($ending)-1);
  }
  if($ending[0]=='o' && ereg('[aiueo]y'.$ending.'$', $last))
  {
    return substr($word, 0, strlen($word)-strlen($ending)-1);
  }
  
  return ereg_replace($ending.'$', '', $word);
}

function matches($key, $word)
{
//  $key = str_replace(' ', '', $key);
//  $word= str_replace(' ', '', $word);
  
  global $debug;
  if($key[0] == '^')
  {
    //if($debug)print 'comparing "'.$word.'" and "'.substr($key, 1).'"<br>';
    if(ereg($key.'$', $word)
    || ereg(substr($key,1).'$', $word)
    || ereg(' '.substr($key, 1).'$', $word))
    {
      //if($debug)print '--> MATCH<br>';
      return true;
    }
    return false;
  }
  $off = mask_off($key, $word);
  if(!is_array($off))
    $off = array($off);
  
  $c=0;
  foreach($off as $choice)
  {
    $on = mask_on($key, $choice);
    if($debug)
      print "mask_on".++$c."('$key', '$choice') = '$on'<br>";
    if($on == $word)
    {
      if($debug)
        print '--> MATCH<br>';
      return true;
    }
  }
  return false;
}

$functions = array
(
  /* Explanations */
  'int' => array(
             'pass' => '<b>Passive</b>',
             'caus' => '<b>Causative/permissive</b>',
             'pasc' => '<b>Passive causative</b>',
             'prog' => '<b>Progressive</b>',
             'masu' => '<b>Polite</b>',
             'nega' => '<b>Negative</b>',
             'pnega' => '<b>Polite negative</b>',
             'desi' => 'Desiderative',
             'pred1' => 'Predicative / adverbial',
             'pred2' => 'Predicative / adverbial',
             'desu' => 'Prior desu',
             'goza' => 'Prior gozaru',
             'pot'  => 'Potential',
             'imp'  => 'Imperative'
                ),
  'term' => array(
             'pres' => 'Present / attributive',
             'pred3' => 'Something like a na-form',
             'past' => '<b>Past</b> / present perfect',
             'futu' => '<b>Suggestive</b>', /* Future / probable present */
             'prpa' => '<b>Probable past</b>',
             'cond' => 'Present <b>conditional</b>',
             'copa' => '<b>Past conditional</b>',
             'conj' => '<b>Progressive / Imperative</b>',
             'noun' => 'Noun form',
             'pron' => 'Pronoun form'
                 ),
   'misc' => array(
             '1' => 'Class I verb',
             '2' => 'Class II verb',
             'S' => 'Suru-verb',
             'K' => 'Kuru-verb',
             'd' => 'Plain copula',
             'D' => '<b>Polite</b> copula',
             'A' => 'Adjective',
             'G' => 'Honorary verb'
                  ),
  'A' => array(
               'pred1'  => 'ku :2',      // predicative / adverbial
               'pred2'  => 'ku ar:1',    // predicative / adverbial
               'goza'   => 'u gozar:1',  // when adding gozaru
               'desu'   => 'i :D',       // when adding desu
  /* Terminal */
               'pres'  => 'i',           // present / attributive
               'imp'   => 'ite',         // imperative
               'past'  => 'katta',       // past / present perfect
               'futu'  => 'karou',       // future / probable present
               'prpa'  => 'kattarou',    // probable past
               'cond'  => 'kereba',      // present conditional
               'copa'  => 'kattara',     // past conditional
          //   'conj'  => 'kute',        // gerund / conjunctive   -- omit this, it's redundant
               'noun'  => 'sa'           // noun form
              ),
  /* Class I verbs */
  '1' => array(
  /* Intermediate */
               'pot'   => 'e:2',       // potential
               'pass'  => 'are:2',     // passive
               'caus'  => 'ase:2',     // causative/permissive
               'pasc'  => 'asare:2',   // passive causative
               'prog'  => 'te i:2',    // progressive
               'masu'  => 'imas:1',    // polite
               'nega'  => 'ana:A',     // negative
               'pnega' => 'imasen :D', // polite negative
               'desi'  => 'ita:A',     // desiderative
  /* Terminal */
               'pres'  => 'u',         // present / attributive
               'past'  => 'ta',        // past / present perfect
               'futu'  => 'ou',        // future / probable present
               'prpa'  => 'tarou',     // probable past
               'cond'  => 'eba',       // present conditional
               'copa'  => 'tara',      // past conditional
               'conj'  => 'te',        // gerund / conjunctive
               'noun'  => 'i',         // noun form
               'pron'  => 'u no'       // pronoun form
              ),
  /* Class II verbs */
  '2' => array(
  /* Intermediate */
               'pass'  => 'rare:2',    // passive
               'caus'  => 'sase:2',    // causative/permissive
               'pasc'  => 'sare:2',    // passive causative
               'prog'  => 'te i:2',    // progressive
               'masu'  => 'mas:1',     // polite
               'nega'  => 'na:A',      // negative
               'pnega' => 'masen :D',  // polite negative
               'desi'  => 'ta:A',      // desiderative
  /* Terminal */
               'pres'  => 'ru',        // present / attributive
               'past'  => 'ta',        // past / present perfect
               'futu'  => 'ou',        // future / probable present
               'prpa'  => 'tarou',     // probable past
               'cond'  => 'reba',      // present conditional
               'copa'  => 'tara',      // past conditional
               'conj'  => 'te',        // gerund / conjunctive
               'noun'  => '',          // noun form
               'pron'  => 'ru no'      // pronoun form
              ),
  /* KURU */
  'K' => array(
  /* Intermediate */
               'pass'  => '^korare:2',   // passive
               'caus'  => '^kosase:2',   // causative/permissive
               'prog'  => '^kite i:2',   // progressive
               'masu'  => '^kimas:1',    // polite
               'nega'  => '^kona:A',     // negative
               'pnega' => '^kimasen :D', // polite negative
               'desi'  => '^kita:A',     // desiderative
  /* Terminal */
               'pres'  => 'kuru',       // present / attributive
               'past'  => '^kita',       // past / present perfect
               'futu'  => '^kiyou',      // future / probable present
               'prpa'  => '^kitarou',    // probable past
               'cond'  => '^kureba',     // present conditional
               'copa'  => '^kitara',     // past conditional
               'conj'  => '^kite',       // gerund / conjunctive
               'noun'  => '^ki',         // noun form
              ),
  /* SURU */
  'S' => array(
  /* Intermediate */
               'pass'  => '^sare:2',     // passive
               'caus'  => '^sase:2',     // causative/permissive
               'prog'  => '^site i:2',   // progressive
               'masu'  => '^simas:1',    // polite
               'nega'  => '^sina:A',     // negative
               'pnega' => '^simasen :D', // polite negative
               'desi'  => '^sita:A',     // desiderative
  /* Terminal */
               'pres'  => 'suru',       // present / attributive
               'past'  => '^sita',       // past / present perfect
               'futu'  => '^siyou',      // future / probable present
               'prpa'  => '^sitarou',    // probable past
               'cond'  => '^sureba',     // present conditional
               'copa'  => '^sitara',     // past conditional
               'conj'  => '^site',       // gerund / conjunctive
               'noun'  => '^si'          // noun form
              ),
  /* Gozaru-style verbs */
  'G' => array(
  /* Intermediate */
               'masu'  => 'imas:1',    // polite
               'pnega' => 'imasen :D', // polite negative
  /* Terminal */
               'pres'  => 'ru',        // present / attributive
              ),
  /* COPULA as DA */
  'd' => array(
  /* Intermediate */
               'nega' => '^ja na:A',     // negative
  /* Terminal */
               'pres'  => 'da',         // present / attributive
               'past'  => '^datta',      // past / present perfect
               'futu'  => '^darou',      // future / probable present
               'cond'  => '^naba',       // present conditional
               'copa'  => '^dattara',    // past conditional
               'conj'  => 'de',         // gerund / conjunctive
              ),
  /* POLITE COPULA as DESU */
  'D' => array(
  /* Intermediate */
               'nega' => 'dewa arimasen :D', // negative
  /* Terminal */
               'pres'  => 'desu',            // present / attributive
               'past'  => '^desita',          // past / present perfect
               'futu'  => '^desyou',          // future / probable present
               'cond'  => '^nareba',          // present conditional
               'copa'  => '^desitara',        // past conditional
               'conj'  => '^desite'           // gerund / conjunctive
              )
  /* ADJECTIVES */
);

function DeconjAnalyze($s, $goal='')
{
  global $functions;
  global $fails, $nodict, $nofull;
  
  if(ereg('imasen$', $s))
  {
    #$s .= ' ';
    $goal = 'D';
  }
    
  $int  = &$functions['int'];
  $term = &$functions['term'];
  $misc = &$functions['misc'];
  
  $analysis = array();
  $analysis['input']   = $s;
  $analysis['goal']    = $goal;
  $analysis['results'] = array();
  
  $most = 0;
  $might = array();
  /* Find out the terminal ending of the token. */
  foreach($functions as $class => $tab)
  {
    if($class=='int' || $class=='term' || $class=='misc')continue;
    foreach($tab as $key => $mask)
    {
      if($key == 'noun')continue;
      if($int[$key])
      {
        #if(!$goal)continue;
        $thisgoal = ereg_replace('.*:', '', $mask);
        if($goal && $thisgoal != $goal)
          continue;
      }
      else
      {
        if(!$term[$key])continue;
        if($goal)
        {
          if(ereg(' $', $s))$mask .= ' ';
        }
        if($mask=='')continue;
      }

      $m = ereg_replace(':[^:]*$', '', $mask);
      $m = str_replace(' ', '', $m);
      $l = matches($m, $s);
      if($l)
      {
        $l = strlen(trim(str_replace('^', '', $m)));
        if($l > $most)$most = $l;
        $might[] = array('class'=>$class, 'key'=>$key, 'mask'=>$m, 'score'=>$l);
      }
    }
  }
  global$debug;if($debug)print_r($might);
  
  foreach($might as $tab)
  {
    if($tab['score'] < $most && $nofull) continue;
    
    $class = $tab['class'];
    $key   = $tab['key'];
    
    // $s seems now like $class member.

    $k = mask_off($tab['mask'], $s);
    
    if(!is_array($k))
      $k = array($k);
    
    $subanalysis = array();
    $subanalysis['score'] = $tab['score'];
    $subanalysis['input'] = $s;
    $subanalysis['class'] = $class;
    $subanalysis['key']   = $key; /* index to $int[] or $term[] */
    $subanalysis['mask']  = $tab['mask'];
    
    foreach($k as $choice)
    {
      global $piu;if($piu++<20)
      
      /* These are the goals. */
      foreach(array('pres') as $woo)
      {
        $wuu = $functions[$class][$woo];
        if(!$wuu)continue;
        $k = mask_on($wuu, $choice);

        $subanalysis2 = array();
        $subanalysis2['mask']  = $wuu;
        $subanalysis2['input'] = $k;

        if(tavumake(tavubreak($k)) != $k)
        {
          /* Skip this obvious fail */
          if($fails)
          {
            $subanalysis2['failcode'] = 'garbage';
            $subanalysis['radicals'][$choice] = $subanalysis2;
          }
          continue;
        }

        $desc='';
        
        /* Find in dictionary */
        $SQL = 'select japdata.id,dict_id,name,kanar,kanjir from japdata'.
             ' left join japattr on trans_id=japdata.id where kanar='.sqlfix(tsu($k));
        $tmp = mysql_query($SQL);
        $tmp3 = array();
        $kanji_id = array();
        while(($tmp2 = mysql_fetch_array($tmp)))
        {
          $tmp3[] = $tmp2;
          $kanji_id[$tmp2['id']] = $tmp2['id'];
        }
        
        if(!count($tmp3))
        {
          if($nodict)
          {
            $subanalysis2['failcode'] = 'nodict';
            $subanalysis['radicals'][$choice] = $subanalysis2;
          }
          continue;
        }

        /* Poistetaan listasta sellaiset päätelmät
         * jotka sanakirja sanoo vääriksi.
         */
        foreach($tmp3 as $tmp2=>$tmp)
        {
          if($tmp['name'] == 'loc'
          || $tmp['name'] == 's'
          || $tmp['name'] == 'p'
          || $tmp['name'] == 'u'
          || $tmp['name'] == 'g'
          || $tmp['name'] == 'f'
          || $tmp['name'] == 'm'
          || $tmp['name'] == 'h'
          || $tmp['name'] == 'num'
          || ($class != 'A' && ereg('^adj', $tmp['name']))
          || ($class != '1' && ereg('^v2', $tmp['name']))
          || ($class != '2' && ereg('^v1', $tmp['name']))
          || ($class != 'K' && ereg('^vk', $tmp['name']))
          || ($class != 'S' && ereg('^vs-s', $tmp['name']))
          || ($class != 'G' && ereg('^hon', $tmp['name']))
          || (!ereg('^[12KSG]', $class) && ereg('v[12it5]', $tmp['name']))
          || ($tmp['name']=='n' && ereg('^[12KS]$', $class))
            )
          {
            unset($kanji_id[$tmp['id']]);
            unset($tmp3[$tmp2]);
          }
        }
        
        if($class=='A' && $woo=='pres' && !ereg('[aeiou]i$', $k))
        {
          if($fails)
          {
            /* It is not an adjective */
            $subanalysis2['failcode'] = 'noadj';
            $subanalysis['radicals'][$choice] = $subanalysis2;
          }
          continue;
        }
        
        if(!count($kanji_id))
        {
          /* Dictionary says it's not what we want */
          if($nodict)
          {
            $subanalysis2['failcode'] = 'dictmismatch';
            $subanalysis['radicals'][$choice] = $subanalysis2;
          }
          continue;
        }
        
        $dict_id = array();
        foreach($tmp3 as $tmp)$dict_id[$tmp['dict_id']] = $tmp['dict_id'];
        
        $subanalysis2['dicts'] = $dict_id;
        $subanalysis2['failcode'] = 'ok';
        $subanalysis['radicals'][$choice] = $subanalysis2;
      }
      
      /* Subprocess */
      $subtries = DeconjAnalyze($choice, $class);
      if(count($subtries))
        $subanalysis['subtries'] = $subtries;

      /* End of subprocess */
    }
    
    if(isset($subanalysis['subtries'])
    || count($subanalysis['radicals']))
    {
      $analysis['results'][] = $subanalysis;
    }
  }
  
  if(!count($analysis['results']))
    $analysis = array();
  
  return $analysis;
}
