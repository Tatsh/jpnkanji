<?php

function dump($tree, $ind=0)
{
  $c=0;

  $flat = count($tree) == 1;
  if($flat)
    foreach($tree as $key => $value)
      if(is_array($value) || is_object($value))$flat = 0;

  print is_object($tree) ? 'Object' : 'Array';
  if($flat)
    print '(';
  else
  {
    print "\n";
    print str_pad('', $ind);
    $ind += 2;
    //print "(\n".str_pad('', $ind);
    print "( ";
  }
  $autoind = 0;
  foreach($tree as $key => $value)
  {
    if($c++)print ",\n".str_pad('', $ind);
    
    if(is_int($key) && $key == $autoind)
      ++$autoind;
    else
    {
      if(is_string($key))
        $s = "'".$key."'";
      else
        $s = $key;
      $s .= " => ";
    }
    
    print $s;
    if(is_array($value) || is_object($value))
    {
      /*if(strlen($s) > 6)
      { 
        print "\n  ".str_pad('', $ind);
        dump($value, $ind + 3);
      } 
      else*/
        dump($value, $ind + min(3,strlen($s)));
    }
    elseif(is_string($value))
      print "'".$value."'";
    else
      print $value;
  }
  if($flat)
    print ")";
  else
  {
    $ind -= 2;
    print "\n";
    print str_pad('', $ind);
    print ")";
  }
}

include_once 'deconjfun.php';

function Add($p)
{
  global $ret;
  $p = Tsu($p);
  if(ereg('^[^hi]', $p))$ret[] = explode(' ', $p);
  $cmd = "/WWW/hiragana '".$p."'|charconv unihtml utf8 2>/dev/null";
  $k = explode(' ', exec($cmd));
  $ret[] = $k;
}

$ret = Array();
function Handle($masu, $pref)
{
  global $ret;
  $ret[] = Array('', '');
  $s = 'kgszn'.'tdmrh'.'bp';
  for($a=0; $a<12; ++$a)
  {
    $c = $s[$a];
    Add(str_replace('x', $c, 'xi'.$masu.' xu'));
    Add(str_replace('x', $c, 'xe'.$masu.' xeru'));
    Add(str_replace('x', $c, 'xi'.$masu.' xiru'));
  }
  Add('i'.$masu.' u');
}
Handle('masu', '');

print '<? $masut = ';
Dump($ret);
print ';';
