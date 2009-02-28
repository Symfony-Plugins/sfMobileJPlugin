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
        // absolute URL or symfony URL?
        if (is_string($parameters) && preg_match('#^[a-z][a-z0-9\+.\-]*\://#i', $parameters)) {
            return $parameters;
        }

        // relative URL?
        if (is_string($parameters) && '/' == $parameters[0]) {
            return $parameters;
        }

        if (is_string($parameters) && $parameters == '#') {
            return $parameters;
        }

        $route = '';
        $fragment = '';

        if (is_string($parameters)) {
            // strip fragment
            if (false !== ($pos = strpos($parameters, '#'))) {
                $fragment = substr($parameters, $pos + 1);
                $parameters = substr($parameters, 0, $pos);
            }

            list($route, $parameters) = $this->convertUrlStringToParameters($parameters);
        } else if (is_array($parameters)) {
            if (isset($parameters['sf_route'])) {
                $route = $parameters['sf_route'];
                unset($parameters['sf_route']);
            }
        }

        // routing to generate path
        $url = $this->context->getRouting()->generate($route, $parameters, $absolute);
       
       // for DoCoMo
        $request = $this->context->getRequest();
        $agent   = $request->getAttribute('agent');
        $logger  = $this->context->getLogger();
        
        if ($agent->isDoCoMo()) {
            $url .= ((strpos($url, '?') === false) ? '?' : '&') . sfConfig::get('mobile_jp_query_string_for_docomo_uid', 'guid=ON');
        }

        if ($fragment) {
            $url .= '#'.$fragment;
        }

        return $url;
    }
}

