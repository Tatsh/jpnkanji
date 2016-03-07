<?php
/* This file is maintained in ~/src/japkanji/ - do not modify otherwhere */

/*
 * This function translates euc-jp codes to images using Shoudoka.
 * Remember to <a href="http://web.lfw.org/shodouka/">thank Shodouka</a>
 * somewhere on your page.
 *
 * Copyright (C) 1992,2002 Bisqwit (http://bisqwit.iki.fi/)
 */

function ShodoukaStart()
{
  ob_start();
}
function ShodoukaEnd()
{
  $s = ob_get_contents();
  ob_end_clean();
  function lfify($s)
  {
    $k = '<img src="http://www.lfw.org:1717/jis-16/000000/';
    for($c=0;$c<strlen($s); ++$c)
      $k .= dechex(ord($s[$c]) & 0x7F);
    return $k . '.gif" height=16 border=0 width=' . (8*strlen($s)) . ' alt="' . $s . '">';
  }
  $s = str_replace('&#12289;', '¡¢', $s);
  #$s = str_replace("¡¢\n", "¡¢¡¡", $s); // convert space to jis ideographic space
  #$s = str_replace("¡¢\n", "¡¢", $s); // strip space
  print preg_replace('|([ -ÿ]+)|e', "lfify('\\1')", $s);
}

ShodoukaStart();
