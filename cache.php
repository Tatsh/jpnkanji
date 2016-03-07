<?php

/*** Bisqwit's caching and compressing php output filter
 *   Version 1.0.5
 *   Copyright (C) 1992,2003 Bisqwit (http://iki.fi/bisqwit/)
 *
 * Example of usage:
 *  <?
 *   include '/WWW/cache.php';
 *   if(cache_init(604800, array('args'=>$REQUEST_URI, 'version'=>1)))
 *     cache_dump_exit();
 *   // The rest of the original file is here.
 *   // Cache will NOT be flushed automatically upon exit,
 *   // so you have to put:
 *   cache_end();
 *   // to the last line of your script.
 *   // Note also that you must not call exit() without calling cache_end() first.
 *
 *  Functions defined:
 *     int cache_init(int timeout, mixed affections)
 *     void cache_expire(mixed affections)
 *     void cache_disablecompression()
 *     void cache_dump()
 *     void cache_dump_exit()
 *     void cache_end()
 *     void cache_setcompressionlevel(int level)
 *
 * Of course you only want to cache pages which take loads of time to load.
 *
 * The data is cached under /dev/shm/, /tmp/phpcache/ or /tmp/,
 * depending on which of them exists and is writable.
 *
 * Distributed under the following terms:
 *   No warranty. You are free to modify this source and to
 *   distribute the modified sources, as long as you keep the
 *   existing copyright messages intact and as long as you
 *   remember to add your own copyright markings.
 *   You are recommended to inform the latest change maker before you
 *   if you are going to make or have made changes to the code.
 *
 * Inspired by PEAR OutputCompressed -module
 * and Tuner's irc-gallery cache module.
 *
 * Neat features:
 *    gzip -compression (if the browser supports it)
 *    configurable expiration times
 *    HTTP 304 reply (if the browser has cached the page)
 *    Easily changed to use sql database instead of filesystem
 *    Flushable realtime output (uncompressed only)
 */

/* Affections as a table of affecting variables, timeout as seconds. */
/* Timeout=0 = expire never, Timeout<0 = Don't cache, just compress */
/* Return value: 0=Normal, 1=Cached, use cache_dump() */
function cache_init($timeout, $affections=array())
{
  global $cache_encoding, $HTTP_ACCEPT_ENCODING, $cache_gziplevel;
  $cache_encoding = (strpos($HTTP_ACCEPT_ENCODING, "x-gzip") !== false) ? 'x-gzip'
                  : (strpos($HTTP_ACCEPT_ENCODING, "gzip") !== false) ? 'gzip'
                  : '';
  $cache_gziplevel = 9;

  $retval = 0;
  if($timeout >= 0)
  {
    global $cache_enabled;
    $cache_enabled = cache_genhash__($affections);

    $t = cache_gettime__($cache_enabled);
    if($t) /* If it has been cached */
    {
      /* But is too old */
      if($timeout > 0 && time()-$t > $timeout)
      {
        /* Expire it */
        cache_erase__($cache_enabled);
      }
      else
      {
        /* cache_dump() is to be called */
        $retval = 1;
      }
    }
    if(!$retval)
    {
      /* Page is not in cache. */
      cache_add_expireheaders($cache_enabled, time(), $timeout);
    }
  }
  else if(!$cache_encoding)
  {
    /* No caching or compressing */
    cache_define_flush_func__();
    return 0;
  }

  //if(!$retval)
  //  register_shutdown_function('cache_end__');
  // Doesn't work - output doesn't work in shutdown funcs
  
  ob_start();
  ob_implicit_flush(false);
  
  /* If we are caching but not compressing, it could
   * be good idea to redefine the flush() function.
   */
  if(!$cache_encoding)
  {
    cache_define_flush_func__();
  }
  return $retval;
}
/* If you want to use bflush(), disable compression by calling this function.
 * This function throws away (ignores) everything printed before calling it,
 * and restarts the caching of the page.
 */
function cache_disablecompression()
{
  global $cache_enabled, $cache_encoding;
  if(!$cache_encoding)return;
  $cache_encoding = '';
  if(!$cache_enabled)return;
  
  ob_end_clean();
  ob_start();
  cache_define_flush_func__();
}
function cache_setcompressionlevel($c)
{
  global $cache_gziplevel;
  $cache_gziplevel = (int)$c;
  if($cache_gziplevel < 0)$cache_gziplevel = 0;
  else if($cache_gziplevel > 9)$cache_gziplevel = 9;
}
/* Explicitly expires a page from cache. */
function cache_expire($affections=array())
{
  $hashkey = cache_genhash__($affections);
  cache_erase__($hashkey);
}
function cache_dump_exit() { cache_dump(); exit; }
function cache_dump()
{
  global $cache_enabled, $cache_encoding;
  if(!$cache_encoding && !$cache_enabled)return;
  $s = ob_get_contents();
  ob_end_clean();
  
  if($cache_enabled)
  {
    global $HTTP_SERVER_VARS;
    $etagmatch = strstr($HTTP_SERVER_VARS['HTTP_IF_NONE_MATCH'], cache_get_etag__($cache_enabled));
    $moddate = $HTTP_SERVER_VARS['HTTP_IF_MODIFIED_SINCE'];
    if($moddate)$moddate = strtotime($moddate);
    if($moddate)$moddate = cache_gettime__($cache_enabled) > $moddate;
    
    #phpinfo();
    
    if($etagmatch || $moddate)
    {
      header('HTTP/1.0 304 Ihan sama kuin viimeksi');
      exit;
    }
    
    cache_add_expireheaders($cache_enabled, cache_gettime__($cache_enabled));
  }
  if(strlen($s) || !$cache_encoding)
  {
    /* Either something has been output (must recompress to get crc and len ok),
     * or the client doesn't accept compressed data (must uncompress cache).
     */
    cache_output__($s.gzuncompress(substr(cache_get__($cache_enabled), 8)));
  }
  else
  {
    /* Good cache and client accepts compressed */
    $s = cache_get__($cache_enabled);
    cache_gzoutput__(substr($s, 8), substr($s,0,4), substr($s,4,4));
  }
}
function cache_end()
{
  global $cache_encoding, $cache_enabled, $cache_gziplevel;
  if(!$cache_encoding && !$cache_enabled)return;
  
  global $cache_bufsofar;
  
  if($cache_encoding)
    $cache_bufsofar = '';
    
  $s = ob_get_contents();
  ob_end_clean();
  if($cache_enabled)
  {
    $cachedata = $cache_bufsofar.$s;
    $c = gzcompress($cachedata, $cache_gziplevel);
    $crcp = pack('V', crc32($cachedata));
    $lenp = pack('V', strlen($cachedata));
    cache_store__($cache_enabled, $crcp.$lenp.$c);
    if($cache_encoding)
      cache_gzoutput__($c, $crcp, $lenp);
    else
      cache_output__($s);
  }
  else
    cache_output__($s);
  
  $cache_enabled = 0;
}

/* Private function: Outputs gzipped data. */
function cache_gzoutput__($c, $crcp, $lenp)
{
  global $cache_encoding;
  header('Content-Encoding: '.$cache_encoding);
  header('Vary: Accept-Encoding');
  if(strlen($c) < 8) { print 'Fail!'; return; }
  echo "\x1f\x8b\x08\x00\x00\x00\x00\x00", // gzip header
       substr($c, 0, strlen($c)-4), $crcp, $lenp;
  flush();
}
/* Private function: Outputs uncompressed data (and may recompress it) */
function cache_output__($s)
{
  global $cache_encoding;
  if($cache_encoding)
  {
    $c = gzcompress($s, 9);
    $crcp = pack('V', crc32($s));
    $lenp = pack('V', strlen($s));
    cache_gzoutput__($c, $crcp, $lenp);
  }
  else
    print $s;
}
function cache_define_flush_func__()
{
  global $cache_bufsofar;
  $cache_bufsofar = '';
}

/* Ack! flush() can't be redefined. */
function bflush()
{
  global $cache_encoding;
  if($cache_encoding)
  {
    print '**ERROR: bflush() CALLED AND OUTPUT IS SENT COMPRESSED**';
    return;
  }
  global $cache_bufsofar;
  $s = ob_get_contents();
  $cache_bufsofar .= $s;
  ob_end_clean();
  ob_implicit_flush(true);
  print $s;
  ob_start();
  ob_implicit_flush(false);
}

/* Private function: Generates the hash for identifying a page. */
function cache_genhash__($affections)
{
  if($affections == 0 || $affections == array())return 0;
  $hashkey = '';
  if(!is_array($affections))
    $hashkey = $affections;
  else
    foreach($affections as $b)$hashkey .= md5($b);
  return md5($hashkey);
}
function cache_get_etag__($hashkey)
{
  return 'BQT-Cache-'.$hashkey;
}
function cache_add_expireheaders($hashkey, $pagetime, $timeout=-1)
{
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $pagetime).' GMT');
  if($timeout > 0)
    header('Expires: ' . gmdate('D, d M Y H:i:s', $pagetime+$timeout).' GMT');
  elseif(!$timeout)
    header('Expires: Tue, Jan 19 2038 05:00:00 GMT'); // Hopefully very late anyway
  header('ETag: '.cache_get_etag__($hashkey));
}
/* Private function: Returns the storage filename for hash. */
function cache_filename__($hashkey)
{
  global $cache_dirname;
  if(!$cache_dirname)
  {
    $dirs = array('/dev/shm/', '/tmp/phpcache/', '/tmp/');
    /* Pass 1: Try finding a writable directory. */
    foreach($dirs as $dir)
      if(file_exists($dir) && is_dir($dir) && is_writable($dir))
      {
        $cache_dirname = $dir;
        break;
      }
    if(!strlen($cache_dirname))
    {
      /* Optional pass 2: Try creating a writable directory. */
      foreach($dirs as $dir)
        if(@mkdir($dir, 0755))
        {
          $cache_dirname = $dir;
          break;
        }
      if(!strlen($cache_dirname))
      {
        /* Ouch. Panic! */
        die('Ouch, that really HURT!');
      }
    }
  }
  return $cache_dirname.$hashkey;
}
/* Private functions: Returns the modification date of file. 0 means miss. */
function cache_gettime__($hashkey)
{
  $fn = cache_filename__($hashkey);
  if(!file_exists($fn))return 0;
  return filemtime($fn);
}
/* Private functions: Erase the cached file. */
function cache_erase__($hashkey)
{
  @unlink(cache_filename__($hashkey));
}
/* Private functions: Modify (or make) the file. */
function cache_store__($hashkey, $data)
{
  $fp = fopen(cache_filename__($hashkey), 'wb');
  fwrite($fp, $data);
  fclose($fp);
}
/* Private functions: Retrieve the file. */
function cache_get__($hashkey)
{
  $fn = cache_filename__($hashkey);
  $fp = fopen($fn, 'rb');
  $s = fread($fp, filesize($fn));
  fclose($fp);
  return $s;
}
/* Initialize cache internal variables to a sane state */
$cache_encoding = ''; /* x-gzip or gzip or ' */
$cache_enabled  = 0;  /* caching or not */
$cache_bufsofar = ''; /* gathered buffer (via bflush) */
$cache_dirname  = ''; /* cache tempfile directory */
