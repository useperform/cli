<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DocsCommand extends Command
{
    protected function configure()
    {
        $this->setName('docs')
            ->setDescription('Search the docs and show the results in your web browser')
            ->addArgument('term', InputArgument::IS_ARRAY, 'The term to search for')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $terms = $input->getArgument('term');
        if (empty($terms)) {
            $q = new Question('Search term: ');
            $terms = explode(' ', $this->getHelper('question')->ask($input, $output, $q));
        }

        $query = urlencode(implode('+', $terms));
        $url = sprintf('https://useperform.com/docs/search.html?q=%s&check_keywords=yes&area=default', $query);

        foreach (['xdg-open', 'open'] as $cmd) {
            if ($this->commandExists($cmd)) {
                exec($cmd.' '.$url);

                return;
            }
        }

        $output->writeln(['Unable to open a web browser. Visit this URL in a web browser:', $url]);
    }

    private function commandExists($command)
    {
        exec('which '.$command, $output, $exitCode);

        return 0 === $exitCode;
    }
}
