<?php

namespace Perform\Cli;

use Symfony\Component\Console\Application as BaseApplication;
use Pimple\Container;

/**
 * Application.
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

    public function getContainer()
    {
        return $this->container;
    }
}
