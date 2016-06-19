<?php

namespace Perform\Cli;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Symfony\Component\Yaml\Yaml;
use Perform\Cli\FileCreator;
use Perform\Cli\Twig\Extension\ConfigExtension;

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

        $c['config'] = function ($c) {
            return new \SpeedyConfig\Config();
        };

        $c['twig.extension.config'] = function ($c) {
            return new ConfigExtension($c['config']);
        };

        $c['twig'] = function ($c) {
            $env = new \Twig_Environment($c['twig.loader'], []);
            $env->addExtension($c['twig.extension.config']);

            return $env;
        };

        $c['file_creator'] = function ($c) {
            return new FileCreator($c['twig']);
        };
    }
}
