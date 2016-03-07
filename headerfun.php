<?php

function AddHTTPheader($s)
{
  @header($s);
  $_SERVER['headers'][] = $s;
}
