<?php

/* SHORTCUT for convenience of radicals.php */

if($HTTP_GET_VARS['bushu'])
{
  $HTTP_GET_VARS['sbushusel'] = $HTTP_GET_VARS['bushu'];
  $HTTP_GET_VARS['sbushu'] = 1;
}

class BushuSearch extends SearchMethod
{
  function GetVars() { return array('sbushusel', 'sbushu'); }
  function FormTitle()
  {
    return 'Bushu search';
  }
  function ResultsTitle()
  {
    global $HTTP_GET_VARS;
    return 'Bushu '.$HTTP_GET_VARS['sbushusel'];
  }
  function DisplayForm()
  {
    print '<input type=checkbox name=sbushu>';
    print 'Select by Bushu radical number: <select name=sbushusel>';

    $radical = array();
    $tmp = mysql_fetch_all(mysql_query('select jiscode from kanjijapan where type=2'));
    foreach($tmp as $tab)
    {
      $tmp2 = mysql_fetch_array(mysql_query('select utf8kanji,radb from japkanji where jiscode='.$tab['jiscode']));
      $radical[$tmp2['radb']] = $tmp2['utf8kanji'];
    }
    
    for($c=1; $c<=214; $c++)
    {
      echo '<option value=', $c, '>';
      echo $c;
      if(isset($radical[$c]))echo ' ', $radical[$c];
      echo '</option>';
    }
    print '</select><br>';
  }
  function GetSQL()
  {
    global $HTTP_GET_VARS;
    return 'radb='.sqlfix($HTTP_GET_VARS['sbushusel']);
  }
  function HaveResults()
  {
    global $HTTP_GET_VARS;
    return $HTTP_GET_VARS['sbushu'];
  }
};
