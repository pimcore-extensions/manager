<?php

namespace Manager\Console\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PermissionsCommand extends AbstractCommand
{
    /**
     * configure command.
     */
    protected function configure()
    {
        $this
            ->setName('manager:permissions')
            ->setDescription('Set permissions required to run \'composer\' with web server user.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Setting permissions...</comment>');

        $basePath = PIMCORE_DOCUMENT_ROOT;
        $vendorPath = $basePath . DIRECTORY_SEPARATOR . 'vendor';

        $writablePaths = [
            implode(DIRECTORY_SEPARATOR, [$basePath, 'plugins']),
            implode(DIRECTORY_SEPARATOR, [$basePath, 'composer.json']),
            implode(DIRECTORY_SEPARATOR, [$basePath, 'composer.lock']),
            implode(DIRECTORY_SEPARATOR, [$vendorPath]),
        ];

        foreach ($writablePaths as $path) {
            $output->write("$path... ");
            try {
                $this->makeWritable($path);
                $output->writeln('<info>OK</info>');
            } catch (\Exception $e) {
                $output->writeln('<error>FAILED</error>');
            }
        }
        $output->writeln('<comment>done</comment>');
    }

    protected function makeWritable($path)
    {
        if (is_file($path)) {
            if (!@chmod($path, 0666)) {
                throw new \Exception('chmod failed for ' . $path);
            }
            return;
        }

        if (is_dir($path)) {
            if (!@chmod($path, 0777)) {
                throw new \Exception('chmod failed for ' . $path);
            }
            $iterator = new \IteratorIterator(new \DirectoryIterator($path));
            foreach ($iterator as $item) {
                if (!$item->isDot()) {
                    self::makeWritable($item->getPathName());
                }
            }
        }
    }
}
