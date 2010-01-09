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
          $agent = sfMobileJPUtils::getAgent();
          
          if ($agent->isDoCoMo() || $agent->isSoftBank() || $agent->isEZweb()) {
              $logger->debug('{sfMobileJPFilter} request is mobile user.');
              if (sfConfig::get('sf_mobile_jp_is_required_uid', false)) {
                  $logger->debug('{sfMobileJPFilter} uid is required');
                  $uid = sfMobileJPUtils::getUID();
                  if (is_null($uid) && $agent->isDoCoMo()) {
                      $queryString = sfConfig::get('sf_mobile_jp_query_string_for_docomo_uid', 'guid=ON');
                      list($uidType, $uidValue) = split('=', $queryString);
                      
                      if (! $request->hasParameter($uidType)) {
                          $logger->debug('{sfMobileJPFilter} No UID!');
                          $url  = $request->getUri();
                          $url .= ((strpos($url, '?') === false) ? '?' : '&') . $queryString;
                          $logger->debug('{sfMobileJPFilter} redirect to ' . $url);
                          return $context->getController()->redirect($url);
                      }
                  }
                  
                  $logger->debug('{sfMobileJPFilter} uid is "' . $uid . '"');
                  $request->setAttribute('sf_mobile_jp_uid', $uid);

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
                  #$response->setHttpHeader('Cache-Control', 'no-cache,must-revalidate');
                  #$response->setHttpHeader('Cache-Control', 'post-check=0,pre-check=0', false);
                  $response->setHttpHeader('Cache-Control', 'no-cache');
                  $response->setHttpHeader('Cache-Control', 'no-store', false);
                  $response->setHttpHeader('Cache-Control', 'must-revalidate', false);
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

