<?php
/**
 * sfFrontWebMobileJPController for DoCoMo imodeID
 *
 *
 * @author Masahiro Funakoshi <mfunakoshi@gmail.com>
 */
class sfFrontWebMobileJPController extends sfFrontWebController
{
  public function genUrl($parameters = array(), $absolute = false)
  {
    $url   = parent::genUrl($parameters, $absolute);
    $request = $this->context->getRequest();
    $agent = $request->getAttribute('agent');
    
    if ($agent->isDoCoMo())
      $url = $this->addParametersByURL($url, sfConfig::get('sf_mobile_jp_query_string_for_docomo_uid', 'guid=ON')); 

    $logger = $this->context->getLogger();
    $logger->debug('{' . __CLASS__ . '} ' . $url);
    
    return $url;
  }

  /**
   * Redirects the request to another URL.
   *
   * @param string $url         An existing URL
   * @param int    $delay       A delay in seconds before redirecting. This is only needed on
   *                            browsers that do not support HTTP headers
   * @param int    $statusCode  The status code
   */
  public function redirect($url, $delay = 0, $statusCode = 302)
  {
    $url = $this->genUrl($url, true);
    
    if ((bool)ini_get('session.use_trans_sid')) {
      $url = $this->addParametersByURL($url, SID);
    }
  
    parent::redirect($url, $delay, $statusCode);
  }

  private function addParametersByURL($url, $queryString)
  {
    $vars   = parse_url($url);
    $querys = null;
    parse_str(@$vars['query'] . '&' . $queryString, $querys);
    $query = http_build_query($querys);
    
    $returnURL = '';
    if (array_key_exists('scheme'   , $vars)) $returnURL  = $vars['scheme'] . '://' . $vars['host'];
    if (array_key_exists('path'     , $vars)) $returnURL .= $vars['path'];
    $returnURL .= '?' . $query;
    if (array_key_exists('fragment' , $vars)) $returnURL .= '#' . $vars['fragment'];
 
    return $returnURL;
  }
}

