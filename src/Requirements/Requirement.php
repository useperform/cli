<?php

namespace Perform\Cli\Requirements;

use Symfony\Component\Process\Process;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Requirement
{
    protected $check;
    protected $error;
    protected $fix;

    public function __construct($checker, $error, $fix)
    {
        $this->checker = $checker;
        $this->error = $error;
        $this->fix = $fix;
    }

    public static function extension($extension)
    {
        $checker = function() use ($extension) {
            return extension_loaded($extension);
        };
        $error = sprintf('The %s extension is not loaded.', $extension);
        $fix = sprintf('Install and enabled this extension using your system\'s package manager.');

        return new static($checker, $error, $fix);
    }

    public static function program($program, $error, $fix)
    {
        $checker = function() use ($program) {
            $p = new Process("which {$program}");

            return $p->run() === 0;
        };

        return new static($checker, $error, $fix);
    }

    public function check()
    {
        return (bool) call_user_func($this->checker);
    }

    public function getError()
    {
        return $this->error;
    }

    public function getFix()
    {
        return $this->fix;
    }
}
