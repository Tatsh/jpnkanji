<?php
/* This file is maintained in ~/src/japkanji/ - do not modify otherwhere */

/*** Bisqwit's compressing php output filter
 *   Version 1.0.2
 *   Copyright (C) 1992,2002 Bisqwit (http://bisqwit.iki.fi/)
 */

function GzCompressStart()
{
  global $cache_encoding;
  global $HTTP_ACCEPT_ENCODING;
  $cache_encoding = (strpos($HTTP_ACCEPT_ENCODING, "x-gzip") !== false) ? 'x-gzip'
                  : (strpos($HTTP_ACCEPT_ENCODING, "gzip") !== false) ? 'gzip'
                  : '';
  if($cache_encoding)ob_start();
}

function GzCompressEnd()
{
  global $cache_encoding;
  
  if($cache_encoding)
  {
    $s = ob_get_contents();
    ob_end_clean();
    
    $c = gzcompress($s, 9);
    $crcp = pack('V', crc32($s));
    $lenp = pack('V', strlen($s));

    header('Content-Encoding: '.$cache_encoding);
    header('Vary: Accept-Encoding');
    echo "\x1f\x8b\x08\x00\x00\x00\x00\x00", // gzip header
         substr($c, 0, strlen($c)-4), $crcp, $lenp;
    flush();
  }
}

GzCompressStart();
