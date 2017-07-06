<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Perform\Cli\Requirements\Requirement;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RequirementsCommand extends Command
{
    protected function configure()
    {
        $this->setName('requirements')
            ->setDescription('Check your system meets the requirements for running Perform.')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->checkRequired($output);

        if (!empty($errors)) {
            $this->printRequiredFailures($output, $errors);
        }

        $recommended = $this->checkRecommended($output);
        if (!empty($recommended)) {
            $this->printRecommendedFailures($output, $recommended);
        }

        $output->writeln(['']);

        if (!empty($errors)) {
            $msg = sprintf('<fg=red>%s</> %s failed',
                           count($errors),
                           count($errors) === 1 ? 'requirement' : 'requirements');

            $msg .= empty($recommended) ? '.'
                 : sprintf(', plus <fg=yellow>%s</> %s.',
                           count($recommended),
                           count($recommended) === 1 ? 'recommendation' : 'recommendations');

            $output->writeln($msg);

            return 1;
        }

        if (empty($recommended)) {
            $output->writeln('This system can run Perform correctly.');

            return;
        }

        $output->writeln(sprintf('<fg=yellow>%s</> recommendations.', count($recommended)));
        $output->writeln('This system can run Perform, but you should consider the recommendations to use it optimally.');
    }

    protected function checkRequired(OutputInterface $output)
    {
        $output->writeln(['Checking requirements...', '']);
        $failures = [];
        foreach ($this->getRequired() as $req) {
            if ($req->check()) {
                $output->write('<fg=green>.</>');
                continue;
            }
            $failures[] = $req;
            $output->write('<fg=red>E</>');
        }
        $output->writeln(['', '']);

        if (empty($failures)) {
            $output->writeln(['<fg=green>PASS</>', '']);

            return;
        }
        $output->writeln(['<fg=red>FAIL</>', '']);

        return $failures;
    }

    protected function getRequired()
    {
        $reqs = [];
        foreach (['gd', 'dom', 'pdo', 'xml', 'zip'] as $ext) {
            $reqs[] = Requirement::extension($ext);
        }

        $reqs[] = Requirement::program('npm',
                                       'The npm package manager was not found.',
                                       'Install nodejs, which should provide npm.');

        return $reqs;
    }

    protected function printRequiredFailures(OutputInterface $output, array $failures)
    {
        $output->writeln(sprintf('%s %s failed:', count($failures), count($failures) === 1 ? 'requirement' : 'requirements'));

        foreach ($failures as $req) {
            $output->writeln([
                '',
                '',
                '<fg=red>'.$req->getError().'</>',
                '',
                'Solution:',
                '    - '.$req->getFix(),
            ]);
        }
    }

    protected function checkRecommended(OutputInterface $output)
    {
        $output->writeln(['', 'Checking recommendations...', '']);
        $failures = [];
        foreach ($this->getRecommended() as $req) {
            if ($req->check()) {
                $output->write('<fg=green>.</>');
                continue;
            }
            $failures[] = $req;
            $output->write('<fg=yellow>R</>');
        }
        $output->writeln(['', '']);

        if (empty($failures)) {
            $output->writeln(['<fg=green>PASS</>', '']);

            return;
        }
        $output->writeln(['<fg=yellow>FAIL</>', '']);

        return $failures;
    }

    protected function getRecommended()
    {
        $reqs = [];

        $reqs[] = new Requirement(function () {
            return date_default_timezone_get() === 'UTC';
        },
            sprintf('PHP\'s timezone should be set to UTC, it is currently "%s".', date_default_timezone_get()),
            'Add "date.timezone = UTC" to php.ini.'
        );

        $reqs[] = Requirement::program('yarn',
                                       'The yarn package manager is not installed.',
                                       'Install yarn wth "npm install -g yarn" to speed up asset build times.');

        return $reqs;
    }

    protected function printRecommendedFailures(OutputInterface $output, array $failures)
    {
        $output->writeln(sprintf('%s %s:', count($failures), count($failures) === 1 ? 'recommendation' : 'recommendations'));

        foreach ($failures as $req) {
            $output->writeln([
                '',
                '',
                '<fg=yellow>'.$req->getError().'</>',
                '',
                'Solution:',
                '    - '.$req->getFix(),
            ]);
        }
    }
}
