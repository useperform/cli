<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Perform\Cli\Exception\FileException;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\ArrayInput;

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
        //composer create-project
    }
}
