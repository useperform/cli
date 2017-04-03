<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Yaml;

/**
 * ConfigCommand.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigCommand extends Command
{
    protected function configure()
    {
        $this->setName('config')
            ->setDescription('Show loaded configuration.')
            ->addArgument('key', InputArgument::OPTIONAL, 'An optional configuration key')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->isVerbose()) {
            //list loaded config files
        }

        $key = $input->getArgument('key');

        $output->writeln(Yaml::dump($this->get('config')->getRequired($key), 100, 2));
    }
}
