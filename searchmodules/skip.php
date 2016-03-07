<?php

class SkipSearch extends SearchMethod
{
  var $desc = 'SKIP codes (do not utilize this commercially!)';
  function GetVars() { return array('skip1', 'skip2', 'skip3a', 'skip3b', 'skips'); }
  
  function FormTitle()
  {
    return 'Search kanji by SKIP code (do not use for commercial purposes!)';
  }
  function ResultsTitle()
  {
    global $HTTP_GET_VARS;
    $skips = $HTTP_GET_VARS['skips'];
    if(ereg('^[0-9]+-[0-9]+-[0-9]+$', $skips))
      $skip = $skips;
    else
    {
      $skip1 = (int)$HTTP_GET_VARS['skip1'];
      $skip2 = (int)$HTTP_GET_VARS['skip2'];
      $skip3 = $HTTP_GET_VARS[$skip1 == 4 ? 'skip3b' : 'skip3a'];
      $skip = (int)$skip1 . '-' . (int)$skip2 . '-' . $skip3;
    }
    return 'SKIP search '.$skip.' results';
  }
  function DisplayForm()
  {
    echo '<a href="http://bisqwit.iki.fi/kala/kanjidic_doc.html#IREF11">Detailed help</a> ',
         'is available about SKIP codes. It is recommended reading.';

    print '<h3>Step 1. Select the type of your kanji.</h3>';
    
    print '<table>';
    echo '<tr><td><input type=radio name=skip1 value=1 checked>1.</td><td>',
           '<pre class=skip1>',
               '<span class=b>  </span><span class=w>  </span>', "\n",
               '<span class=b>  </span><span class=w>  </span>', "\n",
               '<span class=b>  </span><span class=w>  </span>', "\n",
               '<span class=b>  </span><span class=w>  </span>',
           '</pre>',
         '</td><td>Left-right',
         '</td>';
    echo '<td><input type=radio name=skip1 value=2>2.</td><td>',
           '<pre class=skip1>',
               '<span class=b>    </span>', "\n",
               '<span class=b>    </span>', "\n",
               '<span class=w>    </span>', "\n",
               '<span class=w>    </span>',
           '</pre>',
         '</td><td>Top-bottom',
         '</td>';
    echo '<td><input type=radio name=skip1 value=3>3.</td><td>',
           '<pre class=skip1>',
               '<span class=b>    </span>', "\n",
               '<span class=b> <span class=w>  </span> </span>', "\n",
               '<span class=b> <span class=w>  </span> </span>', "\n",
               '<span class=b>    </span>',
           '</pre>',
         '</td><td>Enclosure (outer-inner)',
         '</td>';
    echo '<td><input type=radio name=skip1 value=4>4.</td><td>',
           '<pre class=skip1>',
               '<span class=b>    </span>', "\n",
               '<span class=b>    </span>', "\n",
               '<span class=b>    </span>', "\n",
               '<span class=b>    </span>',
           '</pre>',
         '</td><td>Solid',
         '</td></tr>';
    print '</table>';
    
    print '<h3>Step 2. Count strokes in the shaded part</h3>';
    print 'Count of strokes: <input type=text name=skip2 size=3 maxlength=3>';
    
    print '<h3>Step 3a. Count strokes in the blank part (<em>types 1,2,3 only!</em>)</h3>';
    print 'Count of strokes: <input type=text name=skip3a size=3 maxlength=3>';
    
    print '<h3>Step 3b. Identify solid subpattern (<em>type 4 only!</em>)</h3>';
    
    echo '<input type=radio name=skip3b value=1> Top line',
         '<input type=radio name=skip3b value=2> Bottom line',
         '<input type=radio name=skip3b value=3> Through line (vertical)',
         '<input type=radio name=skip3b value=4> No top/bottom/through line';
    
    print '<p><input type=submit value="Find kanji">';
    
  ?><p><small>SKIP (System of Kanji Indexing by Patterns) numbers are derived from the
  New Japanese-English Character Dictionary (Kenkyusha 1990, NTC 1993) and
  The Kodansha Kanji Learner's Dictionary (Kodansha International, 1999).
  SKIP is protected by copyright, copyleft and patent laws.
  The commercial or non-commercial utilization of SKIP in any form is strictly
  forbidden without the written permission of Jack Halpern, the copyright holder.
  Such permission is normally granted.
  Please contact <a href="mailto:jack@kanji.org">jack@kanji.org</a> and/or see <a href="http://www.kanji.org/">http://www.kanji.org</a>.
  </small><?
  }
  function GetSQL()
  {
    global $HTTP_GET_VARS;
    $skips = $HTTP_GET_VARS['skips'];
    if(ereg('^[0-9]+-[0-9]+-[0-9]+$', $skips))
    {
      $ss = explode('-', $skips);
      $skip1 = $ss[0];
      $skip2 = $ss[1];
      $skip3 = $ss[2];
    }
    else
    {
      $skip1 = (int)$HTTP_GET_VARS['skip1'];
      $skip2 = (int)$HTTP_GET_VARS['skip2'];
      $skip3 = (int)$HTTP_GET_VARS[$skip1 == 4 ? 'skip3b' : 'skip3a'];
    }
    
    return '(skip1='.$skip1.
       ' and skip2='.$skip2.
       ' and skip3='.$skip3
         . ')';
  }
  function HaveResults()
  {
    global $HTTP_GET_VARS;
    return isset($HTTP_GET_VARS['skips'])
       || (isset($HTTP_GET_VARS['skip1'])
       &&  isset($HTTP_GET_VARS['skip2'])
       && (isset($HTTP_GET_VARS['skip3a'])
        || isset($HTTP_GET_VARS['skip3b'])));
  }
};
AddSearchMethod('skip', new SkipSearch());
