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
            ->setDescription('Create a new perform application in the current directory')
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
        $output->writeln('<info>Creating application files</info>');
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
            'app/config/config_prod.yml',
            'app/config/parameters.yml.dist',
            'app/config/routing.yml',
            'app/config/routing_dev.yml',
            'app/config/security.yml',
            'app/Resources/PerformCmsBundle/views/scripts.html.twig',
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
            $this->createFile($output, $file, $input->getOption('skip-existing'));
        }

        $this->maybeCreateBundle($input, $output);
    }

    protected function maybeCreateBundle(InputInterface $input, OutputInterface $output)
    {
        $question = new ConfirmationQuestion("Create a new bundle for this app? ", false);
        $create = $this->getHelper('question')->ask($input, $output, $question);
        if (!$create) {
            return;
        }

        $command = $this->getApplication()->find('create:bundle');
        $command->run(new ArrayInput([]), $output);
    }
}
