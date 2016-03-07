<?php

//include_once 'session.php';

$settingscookiename     = 'KANJISEARCHSETTINGS';
$settingscookielifetime = 3600 * 24 * 30; /* 30 days */

class Customize extends SearchMethod
{
  function _order2cookie($order)
  {
    global $orderfields;
    $s = '';
    foreach(explode(',', $order) as $f1)
    {
      $c=0;reset($orderfields);
      while((list($f2,$desc) = each($orderfields)))
      {
        if($f1==$f2)$s .= chr(ord('A') + $c);
        $c++;
      }
    }
    return $s;
  }

  function _cookie2order($cookie)
  {
    global $orderfields;
    $order = '';
    $b = strlen($cookie);
    for($a = 0; $a < $b; ++$a)
    {
      $c=0;reset($orderfields);
      while((list($f2,$desc) = each($orderfields)))
      {
        if($c+ord('A') == ord($cookie[$a]))$order .= ','.$f2;
        $c++;
      }
    }
    return substr($order, 1);
  }

  /* Converts $preferences to a cookie (returns the text, doesn't set cookie) */
  function _prefsave()
  {
    global $preferences, $similarclasses;
    $cookie = $this->_order2cookie($preferences['searchorder']);
    foreach($preferences['similars'] as $f1 => $order)
    {
      $c=0; reset($similarclasses);
      while((list($f2, $desc) = each($similarclasses)))
      {
        if($f1==$f2)$cookie .= chr(ord('a') + $c);
        $c++;
      }
      $cookie .= $this->_order2cookie($order);
    }
    $translations = 0;
    if($preferences['translations']['china'])  $translations += 1;
    if($preferences['translations']['korea'])  $translations += 2;
    if($preferences['translations']['english'])$translations += 4;
    $cookie .= $translations;
    if(!is_array($preferences['max']) || !count($preferences['max']))
      $preferences['max'] = array('similars' => 200, 'results' => 4000);
    foreach($preferences['max'] as $area => $value)
      $cookie .= $area . $value;
    return $cookie;
  }

  /* Converts cookie to an array (returns it, doesn't modify $preferences) */
  function _prefload($s)
  {
    /*

      cookie-format := <searchorder-code>
                       { <similarclass-code> <searchorder-code> }
                       <translations-code>
                       { <maxname> <maxvalue> }
                    ;
      searchordercode := [A-Z]* (index to $orderfields array, A=0)
                      ;
      similarclass-code := [a-z] (index to $similarclasses array, a=0)
                        ;
      translations-code := [0-9]+ (sum of 1=china 2=korea 4=english)
      
      maxname           := [a-z]+
      maxvalue          := [0-9]+

     */
    global $similarclasses;
    $preferences = array();
    $preferences['searchorder'] = $this->_cookie2order(ereg_replace('[^A-Z].*', '', $s));
    $s = ereg_replace('^[A-Z]*', '', $s);
    while(ereg('^[a-z]', $s))
    {
      $c=0;reset($similarclasses);
      $f1 = '';
      while((list($f2,$desc) = each($similarclasses)))
      {
        if($c+ord('a') == ord($s[0]))$f1 = $f2;
        $c++;
      }
      if($f1) $preferences['similars'][$f1] = $this->_cookie2order(ereg_replace('^[a-z]([A-Z]*).*', '\1', $s));
      $s = ereg_replace('^[a-z][A-Z]*', '', $s);
    }

    $translations = ereg('^[0-9]', $s) ? ((int)$s) : -1;
    if($translations & 1)$preferences['translations']['china'] = 1;
    if($translations & 2)$preferences['translations']['korea'] = 1;
    if($translations & 4)$preferences['translations']['english'] = 1;
    $s = ereg_replace('^[0-9]*', '', $s);
    
    while(ereg('^[a-z]', $s))
    {
      $maxname = ereg_replace('[^a-z].*', '', $s);
      $maxvalue = ereg_replace('^[a-z]*([0-9]*).*', '\1', $s);
      
      $preferences['max'][$maxname] = $maxvalue;

      $s = ereg_replace('^[a-z]+[0-9]*', '', $s);
    }
    
    return $preferences;
  }


  var $desc = 'Customize';
  function GetVars()
  {
    global $translations;
    global $similarclasses;
    $vars = array('newsets', 'sorder1', 'sorder2');
    foreach($translations as $a => $b)
      $vars[] = 'strans'.$a;
    foreach($similarclasses as $a => $b)
    {
      $vars[] = 'sorder1' . $a;
      $vars[] = 'sorder2' . $a;
    }
    return $vars;
  }
  function FormTitle()
  {
    return 'Customize settings';
  }
  function ResultsTitle()
  {
    return 'Settings saved';
  }
  function DisplayForm()
  {
    global $preferences, $similarclasses, $orderfields, $translations;

    print 'Note: Your browser must be able to save "cookies" for this to work.';

    print '<input type=hidden name=newsets value=1>';

    function sortsel($varname, $value)
    {
      global $orderfields, $translations;
      
      print '<select name="'.$varname.'">';
      $c=0;
      foreach($orderfields as $field=>$desc)
      {
        if($field == 'similarity'
        && $varname!='sorder1similarity'
        && $varname!='sorder2similarity')continue;
        
        echo '<option value="', $field, '"';
        if($value == $field){$c++;echo ' selected';}
        echo '>', htmlspecialchars($desc), '</option>';
      }
      echo '<option value=0';
      if(!$c)echo ' selected';
      echo '>---</option></select>';
    }

    //echo'<pre>';print_r($preferences);echo'</pre>';
    
    print '<h3>Order for search results</h3>';
     $order = explode(',', $preferences['searchorder']);
     print 'Primary sort key: ';     sortsel('sorder1', $order[0]);
     print '; Secondary sort key: '; sortsel('sorder2', $order[1]);
    print '<br>';
    
    print '<h3>Which similarities to display</h3>';
    echo 'Empty the both sort keys to disable. ',
         'Note that the stroke count and jouyou choices can cause rather big pages.';
    print '<table>';
    foreach($similarclasses as $field => $desc)
    {
      echo '<tr><th valign=top rowspan=2>', htmlspecialchars($desc);
      echo '</th><td>';
       $order = explode(',', $preferences['similars'][$field]);
       print 'Primary sort key:</td><td>';   sortsel('sorder1'.$field, $order[0]);
       print '</td></tr><tr><td>';
       print 'Secondary sort key:</td><td>'; sortsel('sorder2'.$field, $order[1]);
      print '</td></tr>';
    }
    print '</table>';
    
    print '<h3>Which languages/romanizations to display</h3>';
    
    foreach($translations as $code => $desc)
    {
      echo '<input name="strans', $code, '" type=checkbox';
      if($preferences['translations'][$code])echo ' checked';
      echo '> ', htmlspecialchars($desc), '<br>';
    }
    
    print '<input type=submit value="Apply">';
    
    print '<p>Note: For some reason, Mozilla seems to completely
              ignore the <em>selected</em> options this page has
              in the forms and use its own remembered values instead.
              This can make it seem like the settings were not saved
              when you last changed them.
              I do not know a workaround for this.
          ';

    global $noselectsearch;
    $noselectsearch = 1;
  }
  
  function _MakeSortOrder($vars, $default)
  {
    global $orderfields;
    $s = '';
    foreach($vars as $ss)
      if(strlen($ss) && $orderfields[$ss])
        $s .= ',' . $ss;
    if($default)
      $s .= ',' . $default;
    return substr($s, 1);
  }
  
  function DisplayResults()
  {
    global $preferences, $translations, $similarclasses;
    global $HTTP_GET_VARS, $_COOKIE;
    global $settingscookiename;
    global $settingscookielifetime;
    
    //echo'<pre>';print_r($HTTP_GET_VARS);echo'</pre>';
    //exit;
    
    $preferences = array();
    
    foreach($translations as $a => $b)
      if($HTTP_GET_VARS['strans' . $a])
        $preferences['translations'][$a] = 1;
    
    $preferences['searchorder'] =
      $this->_MakeSortOrder(array(
          $HTTP_GET_VARS['sorder1'],
          $HTTP_GET_VARS['sorder2']),
          'freqorder');

    foreach($similarclasses as $a => $b)
    {
      $preferences['similars'][$a] = $this->_MakeSortOrder(array(
        $HTTP_GET_VARS['sorder1'.$a],
        $HTTP_GET_VARS['sorder1'.$b]),
        '');
    }
    echo 'New personal settings applied. Cookie="', $this->_prefsave($preferences), '"';
    //echo'<pre>';print_r($preferences);echo'</pre>';

    $newcookie = $this->_prefsave($preferences);
    if($newcookie != $_COOKIE[$settingscookiename])
    {
      setcookie($settingscookiename, $newcookie, time() + $settingscookielifetime, '/');
    }
  }
  function HaveResults()
  {
    global $HTTP_GET_VARS;
    return isset($HTTP_GET_VARS['newsets']);
  }
};
AddSearchMethod('customize', new Customize());

if($_COOKIE[$settingscookiename])
{
  $c = new Customize;
  $preferences = $c->_prefload($_COOKIE[$settingscookiename]);
  unset($c);
  
  //echo'<pre>';print_r($preferences);echo'</pre>';
}
