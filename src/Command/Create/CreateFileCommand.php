<?php

namespace Perform\Cli\Command\Create;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Perform\Cli\Exception\FileException;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Perform\Cli\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * CreateFileCommand.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateFileCommand extends Command
{
    protected function configure()
    {
        $this->setName('create:file')
            ->setDescription('Create a file in the current app from a template')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'The file to create.'
            )
            ->addOption(
                'skip-existing',
                's',
                InputOption::VALUE_NONE,
                'Don\'t prompt to overwrite files that already exist.'
            )
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $createdMessage = sprintf('Created <info>%s</info>', $file);
        try {
            $this->get('file_creator')->create($file);
            $output->writeln($createdMessage);
        } catch (FileException $e) {
            if ($input->getOption('skip-existing')) {
                return;
            }

            $question = new ConfirmationQuestion("<info>$file</info> exists. Overwrite? ", false);
            //add another option - view a diff by creating a temp file and comparing
            $overwrite = $this->getHelper('question')->ask($input, $output, $question);
            if ($overwrite) {
                $this->get('file_creator')->forceCreate($file);
                $output->writeln($createdMessage);
            }
        }
    }
}
