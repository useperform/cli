<?php

namespace Perform\Cli;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use SpeedyConfig\Config;
use SpeedyConfig\ConfigBuilder;
use SpeedyConfig\Loader\YamlLoader;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Pimple\Psr11\Container as PsrContainer;
use Perform\Cli\Command;

/**
 * PerformCliProvider.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class PerformCliProvider implements ServiceProviderInterface
{
    public function register(Container $c)
    {
        $c['config.loader'] = function ($c) {
            return new YamlLoader($c);
        };

        $c['config.builder'] = function ($c) {
            $builder = new ConfigBuilder($c['config.loader']);
            $home = isset($_SERVER['HOME']) ? $_SERVER['HOME'] : null;
            if ($home) {
                $builder->addOptionalResource($home.'/.config/perform/perform.yml');
            }
            $builder->addOptionalResource(getcwd().'/.perform.yml');

            return $builder;
        };

        $c['config'] = function ($c) {
            return $c['config.builder']->getConfig();
        };

        $c['app'] = function ($c) {
            $app = new Application($c);
            $cmds = [
                // 'config',
                'new',
                // 'requirements',
                // 'setup',
                'symlink',
            ];
            $services = [];
            foreach ($cmds as $cmd) {
                $services[$cmd] = 'cmd.'.$cmd;
            }
            $app->setCommandLoader(new ContainerCommandLoader(new PsrContainer($c), $services));
            // $app->add(new Command\RequirementsCommand());
            // $app->add(new Command\SetupCommand());
            // $app->add(new Command\SymlinkCommand());

            return $app;
        };

        $c['cmd.new'] = function($c) {
            return new Command\NewCommand();
        };

        $c['cmd.symlink'] = function ($c) {
            return new Command\SymlinkCommand($c['config']);
        };
    }
}
