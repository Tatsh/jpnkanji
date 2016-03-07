<?php

/* Copyright Joel Joonatan Kaartinen (jkaartinen@iki.fi).
 * Also known as Jiten on IRCNet.
 * This file is released under the terms of the GNU General 
 * Public License as published by the Free Software Foundation;
 * either version 2 of the License, or (at your option) any later version.
 * a search module for Bisqwit's Kanjidict.
 *
 * There is NO WARRANTY whatsoever. Use it completely at your own risk.
 * Please refer to the file COPYING that should've been distributed with
 * this file for more details.
 */

class ComponentSearch extends SearchMethod
{
  var $desc = "Component search (experimental)";
  var $components;
  var $wherepart;
  var $compstring;
  function FormTitle()
  {
    return 'Component search';
  }
  function ResultsTitle()
  {
    if (!isset($this->components)) {
      foreach (explode('&', $_SERVER['QUERY_STRING']) as $param) {
        $tmp = explode('=', $param);
        if ($tmp[0] == 'component') $this->components[] = $tmp[1];
      }
    }
    if ($_GET['componentfull']) return 'The first '.$_GET['componentresnum'].' matching kanji';
    else return 'The '.$_GET['componentresnum'].' closest kanji';
  }
  function DisplayForm()
  {
    print '<input type="checkbox" name="componentjap" value="1" checked>&nbsp;Search the most common kanji only. ';
    print '<input type="checkbox" name="componentfull" value="1">&nbsp;All components have to match.<br><br>';
    $i = 5;
    $strokes = 0;
    $tables = array();
    $tablesrows = array();
    $tablelinks = array();
    $tmp = mysql_query("select k.utf8kanji, b.partcode, k.strokecount1 as strokes, k.partcount, count(b.jiscode) as count from "
                      ."japkanji k,kanjiparts b,kanjiradstrokes c where b.partcode=k.jiscode and b.partcode=c.partcode "
                      ."group by k.jiscode order by strokes, partcount, count desc;");
    while ($data = mysql_fetch_row($tmp)) {
      if ($i == 5 || $data[2] != $strokes) { 
        while ($i < 5) { $i++; $tables[$strokes] .= '<td bgcolor="#f0f0f0">&nbsp;';}
        if ($data[2] != $strokes) {
          if ($strokes)  $tables[$strokes] .= '</table>';
          $strokes=$data[2];
          $tablelinks[] = $data[2];
          $tables[$strokes] .= '<table cellpadding="2" width="100%"><th bgcolor="#c0c0c0" colspan="5">Components with '.$data[2].' strokes';
          $tablesrows[$strokes]++;
        }
        $tables[$strokes] .= '<tr>';
        $tablesrows[$strokes]++;
        $i = 0;
      }
      $tables[$strokes] .= ($data[3]==1) ? '<td bgcolor="#e8e8f0">' : '<td bgcolor="#f0e8e8">';
      $tables[$strokes] .= '<input type="checkbox" name="component" value="'.$data[1].'">&nbsp;'.$data[0]."\n"; $i++;
    }
    $tables[$strokes] .= '</table>';
    $col1 = ''; $col2 = ''; $col3 = '';
    $count1 = 0; $count2 = 0; $count3 = 0;
    print 'Select by components:<br><table cellpadding="2">';
    foreach ($tablelinks as $index) {
      if ($count1 <= $count2) {
        if ($count1 <= $count3) { $col1 .= $tables[$index]; $count1 += $tablesrows[$index]; }
        else { $col3 .= $tables[$index];  $count3 += $tablesrows[$index];}
      } else {
        if ($count2 <= $count3) { $col2 .= $tables[$index]; $count2 += $tablesrows[$index]; }
        else { $col3 .= $tables[$index]; $count3 += $tablesrows[$index]; }
      }
    }
    echo '<td valign="top">'.$col1.'<td valign="top">'.$col2.'<td valign="top"2>'.$col3;
    echo '</table>';
    print 'Number of results: <input type="text" name="componentresnum" value="50"><br>';
    print '<input type="submit" name="scomponent" value="Find kanji"><input type="reset" value="Clear selections"<br>';
  }
  function GetFullSQL()
  {
    $this->wherepart = array_map(create_function('$a','return "a.partcode=\'".$a."\'";'), $this->components);
    if ($_GET['componentjap']) $japanese = "and (b.frequency>0 or jouyougrade<>0)";
    if ($_GET['componentfull']) $fullmatch = "having count='".count($this->components)."'";
    $tmp = "select b.jiscode, utf8kanji, mnemonic, "
          ."count(distinct a.partcode)*count(distinct a.partcode)/(count(distinct t.partcode)*"
          .count($this->components).") as score, count(distinct a.partcode) as count "
          ."from kanjiparts as a, japkanji as b,kanjiparts as t where a.jiscode=b.jiscode and ("
          .implode(' or ', $this->wherepart)
          .") and b.jiscode=t.jiscode $japanese group by b.jiscode $fullmatch order by score desc limit ".mysql_escape_string($_GET['componentresnum']);
//    print $tmp."<br>";
    return $tmp;
  }
  function HaveResults()
  {
    return $_GET['scomponent'];
  }
  function GetCacheKey() {
    $vars = array_unique(array('componentresnum','componentjap','componentfull','scomponent'));
    $s = sprintf('%08X', crc32('component'));
    foreach ($this->components as $component) {
      $s .= sprintf('%08X', crc32($component));
    }
    foreach($vars as $varname)
      $s .= sprintf('%08X%08X', crc32($varname), crc32($_GET[$varname]));

    $s .= sprintf('%02X', IsPrinting());
    return $s;
  }
};
AddSearchMethod('component', new ComponentSearch());
?>
