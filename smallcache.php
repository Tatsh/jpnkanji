<?php
/* This file is maintained in ~/src/japkanji/ - do not modify otherwhere */

/*** Bisqwit's subcaching class
 *   Version 1.0.2
 *   Copyright (C) 1992,2002 Bisqwit (http://bisqwit.iki.fi/)
 *
 * Usage example:
 *
 *   $version = 1;
 *   $s = sprintf('%08X%08X', crc32($parameter1), crc32($parameter2));
 *   $c = new Cache($s, $version);
 *   if(!$c->DumpOrLog())
 *   {
 *     .. here is the side-effectless function to be cached ..
 *   }
 *   $c->_Cache();
 *   unset($c);
 *
 */

class Cache
{
  var $filename;
  var $active = 0;
  var $filepath = '/tmp/phpcache/kanjidict/';
  var $newversion = 0;
  var $oldversion = 0;
  var $fp = 0;
  function _TryOpen()
  {
    $this->fp = @fopen($this->filename, 'rb+');
    if(!$this->fp)
    {
      $this->fp = @fopen($this->filename, 'wb');
      if(!$this->fp)
      {
        return;
      }
      $this->oldversion = -1;
    }
    else
    {
      $this->oldversion = hexdec(fread($this->fp, 8));
    }
  }
  function _Close()
  {
    if($this->fp) fclose($this->fp);
    $this->fp = 0;
  }
  /* Use this function to invalidate cache
   * keys which will never be generated again!
   */
  function Invalidate($obsoletekey)
  {
    @unlink($this->filepath . md5($obsoletekey));
  }
  function InvalidateOlderThan($timestamp)
  {
    $dir = opendir($this->filepath);
    while(($fn = readdir($dir)))
      if(filemtime($this->filepath.$fn) < $timestamp)
        unlink($this->filepath.$fn);
    closedir($dir);
  }
  function Cache($key, $initversion=0)
  {
    $this->filename = $this->filepath . md5($key);
    $this->SetVersion($initversion);
  }
  function _Cache()
  {
    $this->EndAndSave();
    $this->_Close();
  }
  function SetVersion($number)
  {
    $this->newversion = (int)$number;
  }
  function DumpOrLog()
  {
    if($this->Have()) { $this->Dump(); return 1; }
    $this->Start();
    return 0;
  }
  function Have()
  {
    if(!$this->fp)
    {
      $this->_TryOpen();
      if(!$this->fp)return 0;
    }
    if($this->newversion > $this->oldversion)
      return 0;
    
    return 1;
  }
  function Dump()
  {
    if(!$this->fp)$this->_TryOpen();
    print fread($this->fp, filesize($this->filename) - 8);
    $this->_Close();
  }
  function Start()
  {
    ob_start();
    $this->active = 1;
  }
  function EndAndSave()
  {
    if($this->active)
    {
      $s = ob_get_contents();
      ob_end_flush();
      
      $this->active = 0;
      if(!$this->fp)$this->_TryOpen();
      rewind($this->fp);
      fwrite($this->fp, sprintf('%08X', $this->newversion));
      fwrite($this->fp, $s);
      ftruncate($this->fp, strlen($s) + 8);
      $this->_Close();
    }
  }
};
function CacheInvalidateOlds()
{
  $c = new Cache('');
  $c->InvalidateOlderThan(time() - 3600*24);
  $c->_Cache();
  unset($c);
}
CacheInvalidateOlds();
