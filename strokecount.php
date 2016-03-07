<?php

/* SHORTCUT for convenience */

if($HTTP_GET_VARS['strokes'])
{
  $HTTP_GET_VARS['sstrokesel'] = $HTTP_GET_VARS['strokes'];
  $HTTP_GET_VARS['sstroke'] = 1;
}

class StrokeCountSearch extends SearchMethod
{
  function GetVars() { return array('sstrokesel', 'sstroke'); }
  function FormTitle()
  {
    return 'Stroke count search';
  }
  function ResultsTitle()
  {
    global $HTTP_GET_VARS;
    return $HTTP_GET_VARS['sstrokesel'].' strokes';
  }
  function DisplayForm()
  {
    print '<input type=checkbox name=sstroke>';
    print 'Select by stroke count: <select name=sstrokesel>';
    for($c=1; $c<=30; $c++)echo '<option>', $c, '</option>';
    print '</select><br>';
  }
  function GetSQL()
  {
    global $HTTP_GET_VARS;
    return '(strokecount1='.sqlfix($HTTP_GET_VARS['sstrokesel']).
        ' or strokecount2='.sqlfix($HTTP_GET_VARS['sstrokesel']).
        ' or strokecount3='.sqlfix($HTTP_GET_VARS['sstrokesel']).
           ')';
  }
  function HaveResults()
  {
    global $HTTP_GET_VARS;
    return $HTTP_GET_VARS['sstroke'];
  }
};
