<?php

session_name('kanjidictsess');
@session_start();

function GetSessVar($varname)
{
  return $_SESSION[$varname];
}
 
function SetSessVar($varname, $value)
{
  return $_SESSION[$varname] = $value;
}
 
function DeleteSessVar($varname)
{
  ## unset($_SESSION[$varname]); - ei toimi?
  $_SESSION[$varname] = null;
}
