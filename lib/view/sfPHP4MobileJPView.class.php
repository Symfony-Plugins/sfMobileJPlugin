<?php
class sfPHP4MobileJPView extends sfPHPView
{
    public function configure()
    {
        $agent = $this->context->getRequest()->getAttribute('agent');
        if ($agent->isDoCoMo() || $agent->isSoftBank() || $agent->isEZweb()) {
            $this->configureForMobile($agent);
        } else {
            parent::configure();
        }
    }

    // for mobile
    private function configureForMobile($agent)
    {
        // store our current view
        $this->context->set('view_instance', $this);
        $logger = $this->context->getLogger();

        // require our configuration
        $careerCode  = $this->getCareerCode($agent);
        $configCache = $this->context->getConfigCache();

        $configFile = $configCache->checkConfig('modules/'.$this->moduleName.'/config/view_' . $careerCode . '.yml', true);
        if (is_null($configFile)) {
            $configFile = $configCache->checkConfig('modules/'.$this->moduleName.'/config/view_mobile.yml', true);
        }
        
        if (is_null($configFile)) {
            $configFile = $this->context->getConfigCache()->checkConfig('modules/'.$this->moduleName.'/config/view.yml', true);
        }
        
        if (!is_null($configFile)) {
            require $configFile;
        }
        
        // set template directory
        if (!$this->directory) {
            $dir = $this->context->getConfiguration()->getTemplateDir($this->moduleName, $this->getTemplate());
            if (is_null($dir)) {
                $dir = $this->context->getConfiguration()->getTemplateDir($this->moduleName, null);
            }
            $this->setDirectory($dir);
        }
   
        $dir = $this->directory;
        $template = $this->getTemplate();
        if (is_readable($dir . DIRECTORY_SEPARATOR . $careerCode . DIRECTORY_SEPARATOR . $template)) {
            $this->setDirectory($dir . DIRECTORY_SEPARATOR . $careerCode);
        } else if (is_readable($dir . DIRECTORY_SEPARATOR . 'mobile' . DIRECTORY_SEPARATOR . $template)) {
            $this->setDirectory($dir . DIRECTORY_SEPARATOR . 'mobile');
        }
    }

    private function getCareerCode($agent)
    {
        $code = null;
        switch (true) {
            case $agent->isDoCoMo():
                $code = 'docomo';
                break;
            case $agent->isSoftBank();
                $code = 'softbank';
                break;
            case $agent->isEZweb();
                $code = 'ezweb';
                break;
        }
    
        return $code;
    }
}

