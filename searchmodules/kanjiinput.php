<?php

class KanjiInput extends SearchMethod
{
  var $desc = 'Copypaste or IME';
  function GetVars() { return array('skanji'); }
  function FormTitle()
  {
    return 'Kanji input';
  }
  function ResultsTitle()
  {
    global $HTTP_GET_VARS;
    return 'Search ' . $HTTP_GET_VARS['skanji'] . ' results';
  }
  function DisplayForm()
  {
    print 'Input your kanji here: <input type=text style="font-size:130%" name=skanji size=5 maxlength=2>';
    print ' <input type=submit value="Find kanji">';
    print '<p>Note: You might want to use a real <a href="http://kanjidict.stc.cx/">dictionary</a> instead.';
  }
  function GetSQL()
  {
    global $HTTP_GET_VARS;
    $SQL = 'select * from japkanji where binary utf8kanji='.sqlfix($HTTP_GET_VARS['skanji']);
    return $SQL;
  }
  function DisplayResults()
  {
    foreach(mysql_fetch_all(mysql_query($this->GetSQL())) as $tmp3)
      Show($tmp3);
    SelectSearch();
  }
  function HaveResults()
  {
    global $HTTP_GET_VARS;
    return isset($HTTP_GET_VARS['skanji']);
  }
};
AddSearchMethod('paste', new KanjiInput());
