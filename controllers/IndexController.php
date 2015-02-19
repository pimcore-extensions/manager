<?php


class Manager_IndexController extends Pimcore_Controller_Action_Admin {
    
    public function indexAction () {

        $client = new Packagist\Api\Client();

        $packagistResults =  $client->search("", array("type" => "pimcore-plugin"));
        $results = array();
        
        foreach($packagistResults as $result)
        {
            $results[] = array(
                "name" => $result->getName(),
                "description" => $result->getDescription(),
                "url" => $result->getUrl(),
                "downloads" => $result->getDownloads(),
                "favers" => $result->getFavers(),
                "repository" => $result->getRepository()
            );
        }
        
        $this->_helper->json(array("success" => true, "packages" => $results));
    }
    
    public function installAction()
    {
        $name = $this->getParam("name", null);
        $client = new Packagist\Api\Client();
        
        if(!$name)
            $this->_helper->json(array("success" => false, "message" => "no package name supplied"));
        
        try
        {
            $package = $client->get($name);
        }
        catch(Exception $ex)
        {
            return $this->_helper->json(array("success" => false, "message" => "packagist package with name '$name' not found"));
        }
        
        $versions = $package->getVersions();
        
        foreach($versions as $version=>$infos)
        {
            if($version == "dev-master")
                continue;
            
            $version = $versions[$version];
            break;
        }
        
        if(!$version instanceof Packagist\Api\Result\Package\Version)
            $version = $versions['dev-master'];
        
        if(!$version instanceof Packagist\Api\Result\Package\Version)
            return $this->_helper->json(array("success" => false, "message" => "no version for package with name '$name' not found"));
            
            
        $config = Manager_Composer::getComposerConfiguration();
        
        if(!$config)
            $this->_helper->json(array("success" => false, "message" => "no composer json found"));
            
        if(!is_array($config['require']))
            $config['require'] = array();
        
        $config['require'][$name] = $version->getVersion();
        
        if(Manager_Composer::writeComposerConfiguration($config))
        {
           Manager_Composer::update();
            
            $this->_helper->json(array("success" => true));
        }
        else
            return $this->_helper->json(array("success" => false, "message" => "couldn't write composer json"));
    }
}
