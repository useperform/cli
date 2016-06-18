<?php

namespace Perform\Cli;

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

    public function render($file)
    {
        return $this->twig->render($file.'.twig');
    }
}
