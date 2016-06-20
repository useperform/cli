<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Perform\Cli\Exception\FileException;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;

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
            ->setDescription('Create a new perform application')
            ->addOption('skip-existing', 's', InputOption::VALUE_NONE, 'Don\'t prompt to overwrite files that already exist.')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $files = [
            '.bowerrc',
            '.gitignore',
            'Makefile',
            'bower.json',
            'composer.json',
            'gulpfile.js',
            'package.json',

            'app/autoload.php',
            'app/AppCache.php',
            'app/AppKernel.php',
            'app/config/config.yml',
            'app/config/config_dev.yml',
            'app/config/parameters.yml.dist',
            'app/config/routing.yml',
            'app/config/routing_dev.yml',
            'app/config/security.yml',
            'bin/console',
            'var/cache/.gitkeep',
            'var/logs/.gitkeep',
            'var/sessions/.gitkeep',
            'web/.htaccess',
            'web/app.php',
            'web/app_dev.php',
            'web/assets/scss/app.scss',
            'web/assets/scss/variables.scss',
            'web/assets/scss/vendors.scss',
        ];
        foreach ($files as $file) {
            $this->createFile($input, $output, $file);
        }
    }

    protected function createFile(InputInterface $input, OutputInterface $output, $file)
    {
        $createdMessage = sprintf('Created <info>%s</info>', $file);
        try {
            $this->get('file_creator')->create($file);
            $output->writeln($createdMessage);
        } catch (FileException $e) {
            if ($input->getOption('skip-existing')) {
                return;
            }

            $question = new ConfirmationQuestion("<info>$file</info> exists. Overwrite? ", false);
            //add another option - view a diff
            $overwrite = $this->getHelper('question')->ask($input, $output, $question);
            if ($overwrite) {
                $this->get('file_creator')->forceCreate($file);
                $output->writeln($createdMessage);
            }
        }
    }
}
