<?php

namespace Manager\Console\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;

class RequireCommand extends AbstractCommand
{
    /**
     * configure command.
     */
    protected function configure()
    {
        $this
            ->setName('manager:require')
            ->setDescription('Manager Require')
            ->addOption(
                'pid', 'p',
                InputOption::VALUE_REQUIRED,
                'PID-File'
            )
            ->addOption(
                'require', 'r',
                InputOption::VALUE_REQUIRED,
                'Required Package'
            )
            ;
    }

    /**
     * execute command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pidFile = $input->getOption("pid");
        $require = $input->getOption("require");

        // dump autoload and regenerate composer.lock
        try {
            $composerPath = \Pimcore\Tool\Console::getExecutable("composer");
            $command = $composerPath . ' require -d ' . PIMCORE_DOCUMENT_ROOT . " " . $require;

            $process = new Process($command);
            $process->setTimeout(60);
            //$process->start();

            while ($process->isRunning()) {
                echo $process->getIncrementalOutput();
            }

        } catch (\Exception $e) {
            $this->writeError($e->getMessage());
        }

        @unlink($pidFile);
    }
}
