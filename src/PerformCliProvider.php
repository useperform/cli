<?php

namespace Perform\Cli;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Perform\Cli\Twig\Extension\ConfigExtension;
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
        $c['twig.loader'] = function ($c) {
            return new \Twig_Loader_Filesystem(__DIR__.'/../templates');
        };

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

        $c['twig.extension.config'] = function ($c) {
            $ext = new ConfigExtension($c['config']);
            $ext->registerDefault('app.name.lowercase', function () {
                return strtolower(basename(getcwd()));
            });

            return $ext;
        };

        $c['twig'] = function ($c) {
            $options = [
                'strict_variables' => true,
            ];
            $env = new \Twig_Environment($c['twig.loader'], $options);
            $env->addExtension($c['twig.extension.config']);

            return $env;
        };

        $c['file_creator'] = function ($c) {
            $creator = new FileCreator($c['twig']);
            $creator->registerChmod('bin/console', 0755);

            return $creator;
        };
    }
}
