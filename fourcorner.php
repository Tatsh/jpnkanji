<?php

class FourCornerSearch extends SearchMethod
{
  var $desc = 'Select by four-corner code';
  function GetVars() { return array('corner1', 'corner2', 'corner3', 'corner4', 'corner5', 'corner6'); }
  function FormTitle()
  {
    return 'Select by Four Corner coding';
  }
  function ResultsTitle()
  {
    global $HTTP_GET_VARS;
    $corner1 = $HTTP_GET_VARS['corner1'];
    $corner2 = $HTTP_GET_VARS['corner2'];
    $corner3 = $HTTP_GET_VARS['corner3'];
    $corner4 = $HTTP_GET_VARS['corner4'];
    $corner5 = $HTTP_GET_VARS['corner5'];
    $corner6 = $HTTP_GET_VARS['corner6'];
  
    $corner = ((int)$corner1) . ((int)$corner2) . ((int)$corner3) . ((int)$corner4) . '.' . ((int)$corner5);
    if($corner == "0000.0" && strlen($corner6) >= 4)
    {
      $corner = $corner6;
      $corner6 = "";
    }
    
    $s = 'Search results for Four-corner code "' . $corner;
    if(strlen($corner6) >= 4)$s .= '" or "' . $corner6;
    $s .= '"';
    
    return $s;
  }
  function DisplayForm()
  {
    echo '<a href="http://bisqwit.iki.fi/kala/kanjidic_doc.html#IREF12">Detailed help</a> ',
         'with examples ',
         'is available about the Four Corner coding system. ',
         'It is recommended reading, although I found SKIP ',
         'codes much easier to use...';
    
    $shapes = array
    (
      '0' => '&#20128; (lid)',
      '1' => '&#19968; (horizontal line)',
      '2' => '&#65372; (vertical line)',
      '3' => '&#20022; (dot)',
      '4' => '&#21313; (cross)',
      '5' => '&#12461; (skewer)',
      '6' => '&#21475; (box)',
      '7' => '&#21378; (angle)',
      '8' => '&#20843; (hachi)',
      '9' => '&#23567; (chiisai)'
    );

    echo '<h3>Select the shape for each corner.</h3>',
         '<table><tr><td valign=top rowspan=2>';
    foreach($shapes as $id=>$desc)
      echo $id, ': ', $desc, '<br>';
    
    echo '</td><td valign=top>',
         '<table>';
    foreach(array(0=>'Left top',
                  1=>'Right top',
                  2=>'Left bottom',
                  3=>'Right bottom',
                  4=>'"Fifth"')
            as $cid=>$text)
    {
      print '<tr><td>';
      echo $text, ' corner:';
      print '</td><td>';
      foreach($shapes as $id=>$desc)
      {
        echo '<input type=radio name=corner',$cid,' value=',$id;
        if($id==0)echo ' checked';
        echo '>';
        #print ereg_replace(' .*', '', $desc);
        print $id;
      }
      print '</td></tr>';
    }
    print '</table>';
    print '<p>..or input the code directly here: <input type=text name=corner6 size=8 maxlength=6> (example: 4000.0)';
    print '</td></tr><tr><td valign=bottom>';

    print '<input type=submit value="Find kanji">';
    
    print '</td></tr></table>';
  }
  function GetSQL()
  {
    global $HTTP_GET_VARS;
    $corner1 = $HTTP_GET_VARS['corner1'];
    $corner2 = $HTTP_GET_VARS['corner2'];
    $corner3 = $HTTP_GET_VARS['corner3'];
    $corner4 = $HTTP_GET_VARS['corner4'];
    $corner5 = $HTTP_GET_VARS['corner5'];
    $corner6 = $HTTP_GET_VARS['corner6'];
  
    $corner = ((int)$corner1) . ((int)$corner2) . ((int)$corner3) . ((int)$corner4) . '.' . ((int)$corner5);
    if($corner == "0000.0" && strlen($corner6) >= 4)
    {
      $corner = $corner6;
      $corner6 = "";
    }
    
    $SQL .= '(fourcorner1='.sqlfix($corner).' or fourcorner2='.sqlfix($corner);
    if(strlen($corner6) >= 4)$SQL .= ' or fourcorner1='.sqlfix($corner6).' or fourcorner2='.sqlfix($corner6);
    $SQL .= ')';
    return $SQL;
  }
  
  function HaveResults()
  {
    global $HTTP_GET_VARS;
    return isset($HTTP_GET_VARS['corner1'])
        && isset($HTTP_GET_VARS['corner2'])
        && isset($HTTP_GET_VARS['corner3'])
        && isset($HTTP_GET_VARS['corner4']);
  }
};
AddSearchMethod('4c', new FourCornerSearch());
