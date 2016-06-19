<?php

namespace Perform\Cli;

use Perform\Cli\Exception\FileException;

/**
 * FileCreator
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FileCreator
{
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function create($file)
    {
        if (file_exists($file)) {
            throw new FileException($file.' exists.');
        }

        return $this->forceCreate($file);
    }

    public function forceCreate($file)
    {
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        file_put_contents($file, $this->render($file));
    }

    public function render($file)
    {
        return $this->twig->render($file.'.twig');
    }
}
