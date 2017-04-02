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
            $this->createFile($input, $output, $file);
        }

        $this->createNewBundle($input, $output);
    }

    protected function createNewBundle(InputInterface $input, OutputInterface $output)
    {
        $config = $this->get('twig.extension.config');
        $default = ucfirst($config->config('app.name.lowercase')).'/AppBundle';
        $question = new Question("Bundle name (<info>$default</info>): ", $default);
        $bundleName = $this->getHelper('question')->ask($input, $output, $question);
        $bundleName = trim($bundleName, '/').'/';
        $files = [
            'Controller/PageController.php',
            'Resources/views/base.html.twig',
            'Resources/views/nav.html.twig',
        ];

        $this->createFile($input, $output, 'src/'.$bundleName.str_replace('/', '', $bundleName).'.php');
        foreach ($files as $file) {
            $this->createFile($input, $output, 'src/'.$bundleName.$file);
        }
    }

    protected function createFile(InputInterface $input, OutputInterface $output, $file)
    {
        $command = $this->getApplication()->find('create:file');
        $args = new ArrayInput([
            'file' => $file,
            '--skip-existing' => $input->getOption('skip-existing'),
        ]);
        $command->run($args, $output);
    }
}
