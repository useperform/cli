<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * CreateCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateCommand extends Command
{
    protected function configure()
    {
        $this->setName('create')
            ->setDescription('Create a file in the current app from a template.')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $file = 'app/config.yml';
        $output->writeln(sprintf('Created <info>%s</info>', $file));
    }
}
