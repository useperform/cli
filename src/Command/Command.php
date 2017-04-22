<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * Command.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Command extends BaseCommand
{
    protected function getContainer()
    {
        return $this->getApplication()->getContainer();
    }

    protected function get($name)
    {
        return $this->getContainer()[$name];
    }
}
