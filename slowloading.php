<?php

/*** Bisqwit's caching module - yet another of them!
 *   Version 1.0.1
 *   Copyright (C) 1992,2004 Bisqwit (http://bisqwit.iki.fi/)
 *
 * The idea between this module is to suppress PHP code execution too often.
 *
 * It checks if the page has been loaded recently by some other user,
 * and if it founds so, it gives the cached copy of the page and exits.
 * Otherwise it wishes to be resumed later to save the page content to the cache.
 *
 * The cache is expected to be in: /dev/shm/slowloading
 * where your web server process should have writing permission to.
 *
 * Usage example:
 *
 *   include_once '/WWW/slowloading.php';
 *   ... your file here ...
 *   EndSlowLoading(); // this must be called last.
 *
 * The period of regenerating the cache is set to 5 minutes (300 seconds).
 *
 * Note: This file should be kept as fast-executing and fast-parsed as possible.
 *
 */

include_once '/WWW/possible304.php';
include_once '/WWW/headerfun.php';

function StartSlowLoading()
{
  global $SLOWLOADING_AVOID_304;
  global $SLOWLOADING_MAGIC;
  global $SLOWLOADING_MAX_AGE;
  
  $req = $_SERVER['REQUEST_URI'];
  $key = sprintf('%08X%08X', crc32($req), crc32(serialize($SLOWLOADING_MAGIC)));
  
  $fn = '/dev/shm/slowloading/'.$key;
  $file = $_SERVER['SCRIPT_FILENAME'];
  
  if(file_exists($fn))
  {
    $t = filemtime($fn);
    if(filemtime($file) <= $t)
    {
      # Once in 5 minutes is ok.
      
      $timeout = 300;
      if(isset($SLOWLOADING_MAX_AGE)) $timeout = (int)$SLOWLOADING_MAX_AGE;
      
      if($t >= time() - $timeout)
      {
        $fp = @fopen($fn, 'r');
        if($fp)
        {
          $s = unserialize(fread($fp, filesize($fn)));
          fclose($fp);
          if($s !== false)
          {
            if(!isset($SLOWLOADING_AVOID_304))
            {
              Check304(Array($fn));
            }
            
            if(is_array($s['h'])) foreach($s['h'] as $v) header($v);
            
            header('X-Cached-From-File: '.$fn);
            
            print gzuncompress($s['s']);
            
            if(function_exists('GzCompressEnd'))
            {
              GzCompressEnd();
            }
            exit;
          }
        }
      }
    }
  }

  if(!isset($SLOWLOADING_AVOID_304))
  {
    Check304(Array($file, '/WWW/slowloading.php'));
  }
  
  ob_start();
}

function EndSlowLoading()
{
  global $SLOWLOADING_MAGIC;
  $file = $_SERVER['SCRIPT_FILENAME'];
  $req  = $_SERVER['REQUEST_URI'];
  $key = sprintf('%08X%08X', crc32($req), crc32(serialize($SLOWLOADING_MAGIC)));
  $fn = '/dev/shm/slowloading/'.$key;
  
  $headers = null;
  if(isset($_SERVER['headers']))
  {
    $headers = $_SERVER['headers'];
  }
  
  $s = ob_get_contents();
  header('X-Cached-To-File: '.$fn);
  ob_end_flush();
  
  /* Compression level 9 is used here to save the limited shm space. */
  $s = serialize(Array('r' => $req,
                       'h' => $headers,
                       's' => gzcompress($s, 9)));

  $fp = @fopen($fn, 'w');
  if(!$fp) return;
  fwrite($fp, $s);
  fclose($fp);
  #touch($fn, filemtime($file));
}

StartSlowLoading();
