<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Perform\Cli\File\ComposerModifier;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

/**
 * SetupCommand.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SetupCommand extends Command
{
    protected function configure()
    {
        $this->setName('setup')
            ->setDescription('Run the initial development setup for an application.')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateCurrentDirectory();
        $this->projectDetails($input, $output);
        $this->incenteevParameters($input, $output);
        //substitute vars in config.yml and parameters.yml
        //remove files from project foundation

        if ($this->confirm($input, $output, 'Add additional Perform Bundles?')) {
            $this->runProc($output, './bin/console perform-dev:add-bundle');
        } else {
            //composer scripts will not have run at this point if no additional bundles were added
            $this->runProc($output, 'composer run-script post-update-cmd');
        }

        if ($this->confirm($input, $output, 'Create a new bundle to store your application code?')) {
            $this->runProc($output, './bin/console perform-dev:create:bundle');
        }

        $output->writeln('Downloading assets for the dashboard...');
        $this->runProc($output, './bin/console perform:install --only assets');
    }

    protected function confirm(InputInterface $input, OutputInterface $output, $question, $defaultYes = true)
    {
        $q = new ConfirmationQuestion($question.($defaultYes ? ' (Y/n) ' : ' (y/N) '), $defaultYes);

        return $this->getHelper('question')->ask($input, $output, $q);
    }

    protected function runProc(OutputInterface $output, $cmd)
    {
        $proc = new Process($cmd);
        $proc->setTty(true);
        $this->getHelper('process')->mustRun($output, $proc);
    }

    protected function validateCurrentDirectory()
    {
        if (!file_exists('./bin/console')) {
            throw new \Exception(sprintf('The current directory "%s" doesn\'t seem to be a Perform application.', getcwd()));
        }
    }

    protected function projectDetails(InputInterface $input, OutputInterface $output)
    {
        $config = new ComposerModifier('composer.json');

        $name = $config->getProperty('name');
        if (!$name || $name === 'perform/project-foundation') {
            $q = new Question('Composer project name, e.g. superco/app: ');
            $config->update(['name' => $this->getHelper('question')->ask($input, $output, $q)]);
        }

        $description = $config->getProperty('description');
        if (!$description || $description === 'Foundation Symfony project using Perform.') {
            $q = new Question('Composer project description: ');
            $config->update(['description' => $this->getHelper('question')->ask($input, $output, $q)]);
        }
    }

    protected function incenteevParameters(InputInterface $input, OutputInterface $output)
    {
        $config = new ComposerModifier('composer.json');
        $cmd = 'Incenteev\\ParameterHandler\\ScriptHandler::buildParameters';
        $confirm = $this->confirm($input, $output, 'Do you want to use the incenteev parameter handler to interactively update parameters.yml?', false);

        if ($confirm) {
            $config->update([
                'require' => [
                    'incenteev/composer-parameter-handler' => '^2.0',
                ],
                'extra' => [
                    'incenteev-parameters' => ['file' => 'app/config/parameters.yml'],
                ],
            ]);

            $scripts = $config->getConfig()['scripts'];
            foreach (['post-install-cmd', 'post-update-cmd'] as $type) {
                $s = $scripts[$type];
                $key = array_search($cmd, $s);
                if ($key === false) {
                    $s[] = $cmd;
                }
                //array_values required so json will output an array, not a dict
                $scripts[$type] = array_values($s);
            }
            $config->replace('scripts', $scripts);

            return;
        }

        $config->update([
            'require' => [
                'incenteev/composer-parameter-handler' => null,
            ],
            'extra' => [
                'incenteev-parameters' => null,
            ],
        ]);

        $scripts = $config->getConfig()['scripts'];
        foreach (['post-install-cmd', 'post-update-cmd'] as $type) {
            $s = $scripts[$type];
            $key = array_search($cmd, $s);
            if ($key !== false) {
                unset($s[$key]);
            }
            //array_values required so json will output an array, not a dict
            $scripts[$type] = array_values($s);
        }
        $config->replace('scripts', $scripts);

        //ensure a basic parameters.yml at least exists
        copy('app/config/parameters.yml.dist', 'app/config/parameters.yml');
    }
}
