<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Command.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Command extends BaseCommand
{
    protected function getContainer()
    {
        return $this->getApplication()->getContainer();
    }

    protected function get($name)
    {
        return $this->getContainer()[$name];
    }

    protected function createFile(OutputInterface $output, $file, $skipExisting = false)
    {
        $command = $this->getApplication()->find('create:file');
        $args = new ArrayInput([
            'file' => $file,
            '--skip-existing' => $skipExisting,
        ]);
        $command->run($args, $output);
    }
}
