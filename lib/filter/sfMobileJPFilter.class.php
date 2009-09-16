<?php
/**
 * sfMobileJPFilter
 *
 * @synopsis
 *  <code>
 *  filters.yml
 *  --
 *  mobile:
 *      class: sfMobileJPFilter
 *
 *  actions.class.php
 *  --
 *  $agent = $this->getRequest()->getAttribute('agent');
 *  </code>
 *
 * @author Masahiro Funakoshi <mfunakoshi@gmail.com>
 */

class sfMobileJPFilter extends sfFilter
{
    public function execute($filterChain)
    {
        $context    = $this->getContext();
        $request    = $context->getRequest();
        $response   = $context->getResponse();
        $logger     = $context->getLogger();
        $nowModule  = $context->getModuleName();
        $nowAction  = $context->getActionName();

        if ($this->isFirstCall()) {
          $er = error_reporting();
          if ($er > E_STRICT) {
              error_reporting($er - E_STRICT);
          }
          
          $mobileUserAgent = sfConfig::get('sf_mobile_jp_net_ua_mobile_path', 'Net/UserAgent/Mobile.php');
          
          $logger->debug('{sfMobileJPFilter} require Net_UserAgent_Mobie path : ' . $mobileUserAgent);
          require_once $mobileUserAgent;
          
          $agent = Net_UserAgent_Mobile::singleton();
          $request->setAttribute('agent', $agent);
          
          if ($agent->isDoCoMo() || $agent->isSoftBank() || $agent->isEZweb()) {
              $logger->debug('{sfMobileJPFilter} request is mobile user.');
              if (sfConfig::get('sf_mobile_jp_is_required_uid', false)) {
                  $logger->debug('{sfMobileJPFilter} uid is required');
                  $uid = null;
                  if ($agent->isDoCoMo()) {
                      $logger->debug('{sfMobileJPFilter} request user is docomo.');
                      $queryString = sfConfig::get('sf_mobile_jp_query_string_for_docomo_uid', 'guid=ON');
                      list($uidType, $uidValue) = split('=', $queryString);
                      $logger->debug('{sfMobileJPFilter} required (' . $uidType . ')');
                      
                      $uid = strtolower($uidType) == 'guid' ? $agent->getUID() : $request->getParameter($uidType, null);
                      if (empty($uid) || $uid == $uidValue) $uid = null;
                      if (is_null($uid) && !$request->hasParameter($uidType)) {
                          $logger->debug('{sfMobileJPFilter} No UID!');
                          $url  = $request->getUri();
                          $url .= ((strpos($url, '?') === false) ? '?' : '&') . $queryString;
                          $logger->debug('{sfMobileJPFilter} redirect to ' . $url);
                          return $context->getController()->redirect($url);
                      }
                  } else {
                      $uid = $agent->getUID();
                      if (empty($uid)) $uid = null;
                  }
                  
                  $logger->debug('{sfMobileJPFilter} uid is "' . $uid . '"');
          
                  $noUidOkPages = sfConfig::get('sf_mobile_jp_no_uid_ok_pages', array());
          
                  if (is_null($uid) && !in_array("{$nowModule}/{$nowAction}", $noUidOkPages)) {
                      if (sfConfig::get('sf_mobile_jp_ng_nonmobile_url', null)) {
                          return $context->getController()->redirect(sfConfig::get('sf_mobile_jp_ng_uid_url'));
                      } else {
                          throw new sfError404Exception('no uid..');
                      }
                  }
              }
              
              $isCache = sfConfig::get('sf_mobile_jp_is_browser_cache', false);
              if (!$isCache) {
                  $response->setHttpHeader('Expires'      , 'Thu, 01 Dec 1994 16:00:00 GMT');
                  $response->setHttpHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
                  $response->setHttpHeader('Cache-Control', 'no-cache,must-revalidate');
                  $response->setHttpHeader('Cache-Control', 'post-check=0,pre-check=0', false);
                  $response->setHttpHeader('Pragma'       , 'no-cache');
              }
             
              // defaulr content-type
              if ($agent->isDoCoMo()) {
                  $response->setContentType('application/xhtml+xml');
              }
          } else if (sfConfig::get('sf_mobile_jp_is_ng_nonmobile', false)) {
              $url = sfConfig::get('sf_mobile_jp_ng_nonmobile_url', null);
              if ($url !== "{$nowModule}/{$nowAction}") {
                  return $context->getController()->redirect($url);
              }
          }
        }
        
        $filterChain->execute();
    }
}

