<?php
/* This file is maintained in ~/src/japkanji/ - do not modify otherwhere */

function utf8str($unicode)
{
  if($unicode < 0x80)return                                                                                    chr(        $unicode      );
  if($unicode < 0x800)return                                                chr(0xC0 +  ($unicode >> 6)    ) . chr(0x80 + ($unicode & 63));
  if($unicode < 0x10000)return          chr(0xE0 +  ($unicode >> 12)    ) . chr(0x80 + (($unicode >> 6)&63)) . chr(0x80 + ($unicode & 63));
  return chr(0xF0 + ($unicode >> 18)) . chr(0x80 + (($unicode >> 12)&63)) . chr(0x80 + (($unicode >> 6)&63)) . chr(0x80 + ($unicode & 63));
}
