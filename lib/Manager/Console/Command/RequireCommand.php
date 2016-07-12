<?php

namespace Manager\Console\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Tool\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->setDescription('Install package using \'composer require\'')
            ->addOption(
                'pid', 'p',
                InputOption::VALUE_REQUIRED,
                'PID-File'
            )
            ->addOption(
                'require', 'r',
                InputOption::VALUE_REQUIRED,
                'Required Package'
            );
    }

    /**
     * Install package using 'composer require'.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pidFile = $input->getOption('pid');
        $require = $input->getOption('require');

        try {
            /**
             * TODO consider direct/programmatic usage of composer/composer package
             * @see https://github.com/composer/composer/issues/1906#issuecomment-51632453
             */
            $composerPath = Console::getExecutable('composer');
            if (!$composerPath) {
                $composerPath = Console::getExecutable('composer.phar');
            }
            if (!$composerPath) {
                throw new \Exception('composer executable not found');
            }

            $command = implode(' ', [
                $composerPath,
                'require -d',
                PIMCORE_DOCUMENT_ROOT,
                $require,
                '2>&1'
            ]);
            $output->writeln(sprintf("Installing package <b>%s</b>\n", $require));

            $process = new Process($command);
            $process->setTimeout(0);
            $return = $process->run(function ($type, $data) use ($output) {
                $output->write($data);
            });
            if ($return !== 0) {
                $this->writeError('Process finished with code: ' . $return);
            } else {
                $output->writeln('<br><b style="color: #0a0;">Package installed successfully.</b>');
            }
        } catch (\Exception $e) {
            $this->writeError($e->getMessage());
        }

        @unlink($pidFile);
    }

    public function writeError($message)
    {
        $this->output->writeln(sprintf('<b style="color: #f30;">ERROR: %s</b>', $message));
    }
}
