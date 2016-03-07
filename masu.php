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

$ret = Array(Array('', ''));
function Add($p)
{
  $p = Tsu($p);
  $cmd = "/WWW/hiragana '".$p."'|charconv unihtml utf8 2>/dev/null";
  $k = explode(' ', exec($cmd));
  global $ret;
  $ret[] = $k;
}
$s = 'kgszntdmrhbp';
for($a=0;$a<10;$a++)
{
  $c = $s[$a];
  Add(str_replace('x', $c, 'ximasu xu'));
  Add(str_replace('x', $c, 'xemasu xeru'));
  Add(str_replace('x', $c, 'ximasu xiru'));
}
Add('imasu u');

print '<? $masut = ';
Dump($ret);
print ';';
