<?php

function recursivemakehash($tab)
{
  if(!is_array($tab))
    return md5($tab);
  $p = '';
  foreach($tab as $a => $b)
    $p .= sprintf('%08X%08X', crc32($a), crc32(recursivemakehash($b)));
  return $p;
}
function array_unique2($input)
{
  $dumdum = array();
  foreach($input as $a => $b)
    $dumdum[$a] = recursivemakehash($b);
  $newinput = array();
  foreach(array_unique($dumdum) as $a => $b)
    $newinput[$a] = $input[$a];
  return $newinput;
}
