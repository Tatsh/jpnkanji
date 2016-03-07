<?php

/* SHORTCUT for convenience */

if($HTTP_GET_VARS['jouyou'])
{
  $HTTP_GET_VARS['sgradesel'] = $HTTP_GET_VARS['jouyou'];
  $HTTP_GET_VARS['sgrade'] = 1;
}

class JouyouSearch extends SearchMethod
{
  function GetVars() { return array('sgradesel', 'sgrade'); }
  function FormTitle()
  {
    return 'Jouyou grade search';
  }
  function ResultsTitle()
  {
    global $HTTP_GET_VARS;
    return 'Grade ' . $HTTP_GET_VARS['sgradesel'];
  }
  function DisplayForm()
  {
    print '<input type=checkbox name=sgrade>';
    print 'Select by commonness: <select name=sgradesel>';
    echo '<option value=1>Jouyou grade 1 (very usual)</option>';
    for($c=2; $c<=6; $c++)echo '<option value=', $c, '>Jouyou grade ', $c, '</option>';
    echo '<option value=8>Joyo (general-use)</option>';
    echo '<option value=9>Jinmeiyou (for use in names)</option>';
    echo '<option value="">No applicable category</option>';
    print '</select><br>';
  }
  function GetSQL()
  {
    global $HTTP_GET_VARS;
    return 'jouyougrade='.sqlfix($HTTP_GET_VARS['sgradesel']);
  }
  function HaveResults()
  {
    global $HTTP_GET_VARS;
    return $HTTP_GET_VARS['sgrade'];
  }
};
