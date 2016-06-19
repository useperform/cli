<?php

namespace Perform\Cli;

use Symfony\Component\Console\Application as BaseApplication;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Application
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Application extends BaseApplication
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct('Perform CLI', 1);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelperSet()->get('question');
        $this->container['twig.extension.config']->setConsoleEnvironment($input, $output, $helper);

        return parent::doRun($input, $output);
    }

    public function getContainer()
    {
        return $this->container;
    }
}