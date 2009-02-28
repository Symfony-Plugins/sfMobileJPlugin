<?php
/**
 * sfMobileJPUtils -  utils class
 *
 * @author Masahiro Funakoshi <mfunakoshi@gmail.com>
 */

class sfMobileJPUtils
{
    private static
        $fontSizeVars
            = array(
                  'DoCoMo' => array(
                        1 => 'font-size:xx-small;'
                      , 2 => 'font-size:x-small;'
                      , 3 => 'font-size:small;'
                      , 4 => 'font-size:medium;'
                      , 5 => 'font-size:large;'
                      , 6 => 'font-size:x-large;'
                      , 7 => 'font-size:xx-large;'
                  )
                , 'other' => array(
                        1 => 'font-size:0.6em;'
                      , 2 => 'font-size:0.7em;'
                      , 3 => 'font-size:0.8em;'
                      , 4 => 'font-size:1.0em;'
                      , 5 => 'font-size:1.2em;'
                      , 6 => 'font-size:1.4em;'
                      , 7 => 'font-size:1.6em;'
                  )
              );

    /** 
     * getDocType
     *
     * @return  string  doctype tag
     */
    public static function getDocType()
    {
        $agent = self::getAgent();
        $docType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        if ($agent->isDoCoMo()) {
            $docType = '<!DOCTYPE html PUBLIC "-//i-mode group (ja)//DTD XHTML i-XHTML(Locale/Ver.=ja/1.0) 1.0//EN" "i-xhtml_4ja_10.dtd">';
        } else if ($agent->isSoftbank()) {
            $docType = '<!DOCTYPE html PUBLIC "-//J-PHONE//DTD XHTML Basic 1.0 Plus//EN" "xhtml-basic10-plus.dtd">';
        } else if ($agent->isEZweb()) {
            $docType = '<!DOCTYPE html PUBLIC "-//OPENWAVE//DTD XHTML 1.0//EN" "http://www.openwave.com/DTD/xhtml-basic.dtd">';
        }

        return $docType;
    }

    public static function convertFontSize($size = 3)
    {
        $agent = self::getAgent();

        return $agent->isDoCoMo() ? self::$fontSizeVars['DoCoMo'][$size] : self::$fontSizeVars['other'][$size];

    }

    // shortcut method for agent object
    public static function getAgent()
    {
        return sfContext::getInstance()->getRequest()->getAttribute('agent');
    }

}
