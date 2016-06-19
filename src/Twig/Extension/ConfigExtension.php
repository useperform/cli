<?php

namespace Perform\Cli\Twig\Extension;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use SpeedyConfig\Config;

/**
 * ConfigExtension
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigExtension extends \Twig_Extension
{
    protected $config;
    protected $input;
    protected $output;
    protected $helper;
    protected $defaults = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function setConsoleEnvironment(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $helper;
    }

    public function registerDefault($key, $function)
    {
        $this->defaults[$key] = $function;
    }

    public function getFunctions()
    {
        return [
             new \Twig_SimpleFunction('config', [$this, 'config']),
        ];
    }

    public function config($key)
    {
        if (null === $value = $this->config->get($key)) {
            if (isset($this->defaults[$key])) {
                $default = $this->defaults[$key]();
                $question = new Question(sprintf('<info>%s</info> (%s): ', $key, $default), $default);
            } else {
                $question = new Question(sprintf('<info>%s</info>: ', $key));
            }
            $value = $this->helper->ask($this->input, $this->output, $question);
            $this->config->set($key, $value);
        }

        return $value;
    }

    public function getName()
    {
        return 'config';
    }
}
