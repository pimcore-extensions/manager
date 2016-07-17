<?php

use Packagist\Api\Result\Package\Version;
use Packagist\Api\Client;

use Pimcore\Controller\Action\Admin;

class Manager_IndexController extends Admin
{
    public function indexAction()
    {
        $client = new Client();

        $results = $client->search('', ['type' => 'pimcore-plugin']);
        $packages = [];

        $downloaded = \Manager\Composer::getDownloaded();

        $sortable = ['name', 'description', 'downloads', 'favers'];
        $sort = [];
        if ($this->getParam('sort')) {
            $sort = json_decode($this->getParam('sort'), true);
            if (is_array($sort) && !empty($sort)) {
                $sort = $sort[0];
            }
        }
        if (!in_array($sort['property'], $sortable)) {
            $sort['property'] = 'downloads';
        }
        $direction = strtoupper($sort['direction']) === 'ASC' ? SORT_ASC : SORT_DESC;

        // for array_multisort
        $sorter = [];

        /** @var Packagist\Api\Result\Result $result */
        foreach ($results as $result) {
            if (isset($downloaded[$result->getName()])) {
                continue;
            }
            $package = [
                'name' => $result->getName(),
                'description' => $result->getDescription(),
                'url' => $result->getUrl(),
                'downloads' => $result->getDownloads(),
                'favers' => $result->getFavers(),
                'repository' => $result->getRepository()
            ];
            $packages[] = $package;
            $sorter[] = $package[$sort['property']];
        }

        array_multisort($sorter, $direction, $packages);

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
        } catch (\Exception $ex) {
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
                'message' => "no version found for package '$name'"]);

        try {
            $jobId = \Manager\Composer::installPackage($name . ':' . $version->getVersion());

            return $this->_helper->json(['success' => true, 'jobId' => $jobId]);
        } catch (Exception $e) {
            return $this->_helper->json([
                'success' => false,
                'message' => $e->getMessage()]);
        }
    }

    public function statusAction()
    {
        return $this->_helper->json([
            'status' => \Manager\Composer::getStatus($this->getParam('jobId')),
            'log' => \Manager\Composer::getLog(),
        ]);
    }
}
