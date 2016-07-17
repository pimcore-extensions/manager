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

        // for array_multisort
        $sorters = [
            'name' => [],
            'description' => [],
            'downloads' => [],
            'favers' => [],
        ];

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
            $sorters['name'][] = $result->getName();
            $sorters['description'][] = $result->getDescription();
            $sorters['downloads'][] = $result->getDownloads();
            $sorters['favers'][] = $result->getFavers();
        }

        $sort = [
            'property' => 'downloads',
            'direction' => 'DESC',
        ];
        if ($this->getParam('sort')) {
            $sort = json_decode($this->getParam('sort'), true)[0];
        }

        $sortArray = &$sorters['name'];
        if (isset($sorters[$sort['property']]))
            $sortArray = &$sorters[$sort['property']];
        $direction = strtoupper($sort['direction']) === 'ASC' ? SORT_ASC : SORT_DESC;

        array_multisort($sortArray, $direction, $packages);

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
