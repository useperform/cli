<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputArgument;

/**
 * NewCommand.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class NewCommand extends Command
{
    protected function configure()
    {
        $this->setName('new')
            ->setDescription('Create and configure a new perform application.')
            ->addArgument('directory', InputArgument::REQUIRED, 'The directory to create the application inside')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $this->getDirectory($input, $output);
        $project = 'perform/project-foundation';

        $output->writeln(["Downloading $project...", '']);
        $proc = new Process("composer create-project --stability dev --no-scripts --no-interaction $project $dir");
        $proc->setTty(true);
        $this->getHelper('process')->mustRun($output, $proc);

        $output->writeln(['Running "perform setup"...', '']);
        chdir($dir);
        $cmd = $this->getApplication()->find('setup');
        $cmd->run(new ArrayInput([]), $output);
    }

    public function getDirectory($input, $output)
    {
        $dir = $input->getArgument('directory');

        if (!file_exists($dir)) {
            return $dir;
        }

        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a directory.', $dir));
        }

        $files = new \FilesystemIterator($dir);
        if ($files->valid()) {
            throw new \InvalidArgumentException(sprintf('"%s" is not empty. To setup an already cloned foundation project, run "perform setup".', $dir));
        }

        return $dir;
    }
}
