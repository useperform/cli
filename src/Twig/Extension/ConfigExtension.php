<?php

namespace Perform\Cli\Twig\Extension;

/**
 * ConfigExtension
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigExtension extends \Twig_Extension
{
    public function __construct()
    {
    }

    public function getFunctions()
    {
        return [
             new \Twig_SimpleFunction('config', [$this, 'config']),
        ];
    }

    public function config($key)
    {
        return 'http://example.com';
    }

    public function getName()
    {
        return 'config';
    }
}
