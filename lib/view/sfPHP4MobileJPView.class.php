<?php

class sfPHP4MobileJPView extends sfPHPView
{
    public function configure()
    {
        $agent = $this->context->getRequest()->getAttribute('agent');
        if ($agent->isDoCoMo() || $agent->isSoftBank() || $agent->isEZweb()) {
            $sf_version = defined(SYMFONY_VERSION) ? (int)SYMFONY_VERSION : 1.0;
            if ($sf_version > 1.0 && $sf_version < 1.5) {
              // for 1.1.x - 1.4.x
              $this->configureForMobile($agent);
            } else {
              // for 1.0.x
              $this->configureForMobile10($agent);
            }
        } else {
            parent::configure();
        }
    }

    // for symfony 1.0
    protected function configureForMobile10($agent)
    {
        // store our current view
        $actionStackEntry = $this->getContext()->getActionStack()->getLastEntry();
        if (!$actionStackEntry->getViewInstance())
        {
          $actionStackEntry->setViewInstance($this);
        }
        
        $logger = $this->context->getLogger();
        
        // require our configuration
        $careerCode  = $this->getCareerCode($agent);
        $configCache = sfConfigCache::getInstance();
        
        $configFile = $configCache->checkConfig('modules/'.$this->moduleName.'/config/view_' . $careerCode . '.yml', true);
        if (is_null($configFile)) {
            $configFile = $configCache->checkConfig('modules/'.$this->moduleName.'/config/view_mobile.yml', true);
        }
        
        if (is_null($configFile)) {
            $configFile = $configCache->checkConfig('modules/'.$this->moduleName.'/config/view.yml', true);
        }
        
        if (!is_null($configFile)) {
            require $configFile;
        }
        
        // set template directory
        if (!$this->directory) {
            $dir = sfLoader::getTemplateDir($this->moduleName, $this->getTemplate());
            if (is_null($dir)) {
                $dir = sfLoader::getTemplateDir($this->moduleName, null);
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

    // for symfony 1.2 -
    protected function configureForMobile($agent)
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
            $configFile = $configCache->checkConfig('modules/'.$this->moduleName.'/config/view.yml', true);
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

    protected function getCareerCode($agent)
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

