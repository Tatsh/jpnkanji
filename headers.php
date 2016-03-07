<?php

function Headers($title)
{
  /* The beginning of the page. */
  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">',
       '<html><head><title>',htmlspecialchars($title),'</title>',
       '<meta http-equiv="Content-Type" content="text-html; charset=utf-8">';
  /* Include possible style sheet inclusions here. */
  echo '</head><body>';
}
function NavBar($s)
{
  /* Beginning of the page */
  echo '<hr>';
}
function Epilogue()
{
  /* End of the page, start of possible comments */
  echo '<hr>';
}
function Footers()
{
  /* Final end of page */
  echo '</body></html>';
}
