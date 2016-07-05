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
    protected $chmods = [];

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function registerChmod($file, $mode)
    {
        $this->chmods[$file] = $mode;
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
        $vars = [];
        $template = $file;

        if (preg_match('`src/(.*Bundle)/`', $template, $matches)) {
            $template = preg_replace('`src/.*Bundle/`', 'src/_bundle/', $template);
            $vars['bundle_namespace'] = str_replace('/', '\\', $matches[1]);
            $vars['bundle_namespace_twig'] = str_replace('/', '', $matches[1]);

            if (preg_match('`[a-zA-Z]+Bundle.php$`', $template, $matches)) {
                $template = preg_replace('`[a-zA-Z]+Bundle.php$`', '_bundle.php', $template);
            }
        }

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        file_put_contents($file, $this->twig->render($template.'.twig', $vars));
        if (isset($this->chmods[$file])) {
            chmod($file, $this->chmods[$file]);
        }
    }
}
