<?php

namespace Perform\Cli;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use SpeedyConfig\Config;
use SpeedyConfig\ConfigBuilder;
use SpeedyConfig\Loader\YamlLoader;

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
    }
}
