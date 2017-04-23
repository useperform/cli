<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;

/**
 * InitCommand.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class InitCommand extends Command
{
    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Create and configure a new perform application in the current directory.')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $project = 'perform/project-foundation';
        $output->writeln(["Downloading $project...", '']);
        $proc = new Process("composer create-project --stability dev --no-scripts --no-interaction $project .");
        $proc->setTty(true);
        $this->getHelper('process')->mustRun($output, $proc);

        $cmd = $this->getApplication()->find('setup');
        $cmd->run(new ArrayInput([]), $output);
    }
}
