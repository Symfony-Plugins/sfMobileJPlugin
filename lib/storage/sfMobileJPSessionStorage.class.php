<?php

class sfMobileJPSessionStorage extends sfSessionStorage
{
  public function initialize($options = null)
  {
    $uid = sfMobileJPUtils::getUID();
    
    ini_set('session.use_cookies', 0);
    if (strlen($uid)) {
      $options['session_id'] = md5($uid);
      $options['auto_start'] = true;
    } else {
      $options['auto_start'] = false;
    }

    // initialize parent
    parent::initialize($options);
  }
}
