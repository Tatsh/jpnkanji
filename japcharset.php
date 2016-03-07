<?php

/*** Bisqwit's extempore character set changer for PHP
 *   Copyright (C) 1992,2003 Bisqwit (http://iki.fi/bisqwit/)
 *
 * Version 1.2
 *
 * Requires htmlrecode (http://iki.fi/bisqwit/source/htmlrecode.html)
 * 
 * Usage:
 *
 * <?php
 * 
 * include_once 'japcharset.php';
 *
 * ..
 * // your code here
 * ..
 *
 * // first param: your character set (what you just generated)
 * // second param: desired character set (what will be output)
 * CharSetEnd('iso-8859-1', 'utf-8');
 * // end
 *
 */

function shellfix($s){return "'".str_replace("'", "'\''", $s)."'";}

include_once '/WWW/headerfun.php';

function CharSetStart()
{
  ob_start();
}

function CharSetEnd($inset='euc-jp', $outset='euc-jp')
{
  $s = ob_get_contents();
  ob_end_clean();
  AddHTTPheader('Content-type: text/html; charset='.$outset);
  
  putenv('PATH=/usr/local/bin:'.getenv('PATH'));

  if($inset == $outset)
    print $s;
  else
    passthru(
      'echo '.shellfix($s).
      '|htmlrecode -I'.shellfix($inset).
                 ' -O'.shellfix($outset)
//       .' 2>&1'
           );
}

CharSetStart();
