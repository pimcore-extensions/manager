<?php

use Packagist\Api\Result\Package\Version;

class Manager_IndexController extends Pimcore_Controller_Action_Admin
{
    public function indexAction()
    {
        $client = new Packagist\Api\Client();

        $results = $client->search('', ['type' => 'pimcore-plugin']);
        $packages = [];

        $downloaded = Manager_Composer::getDownloaded();

        /** @var Packagist\Api\Result\Result $result */
        foreach ($results as $result) {
            if (isset($downloaded[$result->getName()])) {
                continue;
            }
            $packages[] = [
                'name' => $result->getName(),
                'description' => $result->getDescription(),
                'url' => $result->getUrl(),
                'downloads' => $result->getDownloads(),
                'favers' => $result->getFavers(),
                'repository' => $result->getRepository()
            ];
        }

        $this->_helper->json(['success' => true, 'packages' => $packages]);
    }

    public function installAction()
    {
        $name = $this->getParam('name', null);

        if (!$name)
            return $this->_helper->json([
                'success' => false,
                'message' => 'no package name supplied']);

        $client = new Packagist\Api\Client();
        try {
            $package = $client->get($name);
        } catch (Exception $ex) {
            return $this->_helper->json([
                'success' => false,
                'message' => "packagist package with name '$name' not found"]);
        }

        $versions = $package->getVersions();

        $version = null;
        foreach ($versions as $version => $infos) {
            // TODO(rafalgalka) check minimum-stability
            if ($version == 'dev-master')
                continue;

            $version = $versions[$version];
            break;
        }

        if (!$version instanceof Version)
            $version = $versions['dev-master'];

        if (!$version instanceof Version)
            return $this->_helper->json([
                'success' => false,
                'message' => "no version for package with name '$name' not found"]);

        try {
            $jobId = Manager_Composer::requirePackage($name . ':' . $version->getVersion());

            return $this->_helper->json(['success' => true, 'jobId' => $jobId]);
        } catch (Exception $e) {
            return $this->_helper->json([
                'success' => false,
                'message' => $e->getMessage()]);
        }
    }

    public function statusAction()
    {
        $jobId = $this->getParam('jobId');

        $status = Manager_Composer::getStatus($jobId);
        $log = Manager_Composer::getLog();

        return $this->_helper->json(['status' => $status, 'log' => $log]);
    }
}
