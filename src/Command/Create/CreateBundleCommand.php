<?php

namespace Perform\Cli\Command\Create;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Perform\Cli\Command\Command;
use Symfony\Component\Console\Question\Question;

/**
 * CreateBundleCommand.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateBundleCommand extends Command
{
    protected function configure()
    {
        $this->setName('create:bundle')
            ->setDescription('Create a bundle with frontend pages for this app')
            ->addArgument(
                'namespace',
                InputArgument::OPTIONAL,
                'Namespace of the new bundle.'
            )
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->get('twig.extension.config');
        $namespace = $input->getArgument('namespace');
        if (!$namespace) {
            $default = ucfirst($config->config('app.name.lowercase')).'/AppBundle';
            $question = new Question("Bundle name (<info>$default</info>): ", $default);
            $namespace = $this->getHelper('question')->ask($input, $output, $question);
        }
        $namespace = trim($namespace, '/').'/';

        $files = [
            'Controller/PageController.php',
            'Resources/views/base.html.twig',
            'Resources/views/nav.html.twig',
        ];

        $this->createFile($output, 'src/'.$namespace.str_replace('/', '', $namespace).'.php');
        foreach ($files as $file) {
            $this->createFile($output, 'src/'.$namespace.$file);
        }
    }
}
