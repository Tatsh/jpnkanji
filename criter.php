<?php

require 'searchmodules/jouyou.php';
require 'searchmodules/strokecount.php';
require 'searchmodules/bushu.php';

class CriterSearch extends SearchMethod
{
  var $desc = 'Select by commonness, stroke count and/or radical number';

  var $jouyou;
  var $stroke;
  var $bushus;
  
  function CriterSearch()
  {
    $this->jouyou = new JouyouSearch();
    $this->stroke = new StrokeCountSearch();
    $this->bushus = new BushuSearch();
  }
  
  function GetVars()
  {
    return array_merge($this->jouyou->GetVars(),
                       $this->stroke->GetVars(),
                       $this->bushus->GetVars());
  }
  
  function FormTitle()
  {
    return 'Search kanji by selectable criters';
  }
  function ResultsTitle()
  {
    $s = 'Search results';
    $titles = array();
    if($this->jouyou->HaveResults()) $titles[] = $this->jouyou->ResultsTitle();
    if($this->stroke->HaveResults()) $titles[] = $this->stroke->ResultsTitle();
    if($this->bushus->HaveResults()) $titles[] = $this->bushus->ResultsTitle();
    if(count($titles))
    {
      $s .= '(';
      for($c=0; $c<count($titles); $c++)
      {
        if($c > 0)$s .= ', ';
        $s .= $titles[$c];
      }
      $s .= ')';
    }
    return $s;
  }
  function DisplayForm()
  {
    //print '<h1>Sorry, this form is malfunctioning at the moment. Kaeritai kudasai.</h1>';
    
    $this->jouyou->DisplayForm();
    $this->stroke->DisplayForm();
    $this->bushus->DisplayForm();
    print '<br><input type=submit value="Find kanji">';
  }
  function GetSQL()
  {
    $SQL = '(';
    $c=0;
    if($this->jouyou->HaveResults()) { if($c++)$SQL .= ' and ';   $SQL .= $this->jouyou->GetSQL(); }
    if($this->stroke->HaveResults()) { if($c++)$SQL .= ' and ';   $SQL .= $this->stroke->GetSQL(); }
    if($this->bushus->HaveResults()) { if($c++)$SQL .= ' and ';   $SQL .= $this->bushus->GetSQL(); }
    $SQL .= ')';
    return $SQL;
  }
  function HaveResults()
  {
    return $this->jouyou->HaveResults()
        || $this->stroke->HaveResults()
        || $this->bushus->HaveResults();
  }
};
AddSearchMethod('criter', new CriterSearch());
