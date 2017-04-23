<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\Table;

/**
 * SymlinkCommand.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SymlinkCommand extends Command
{
    protected function configure()
    {
        $this->setName('symlink')
            ->setDescription('Symlink perform packages in vendor/ to a local checkout')
            ->addArgument('packages', InputArgument::IS_ARRAY, 'The packages to symlink')
            ->addOption('directory', '', InputOption::VALUE_REQUIRED,
                        'The location of the local checkout', '/home/vagrant/perform/perform-bundles')
            ->addOption('reset', '', InputOption::VALUE_NONE, 'Remove the symlink')
            ->addOption('show', '', InputOption::VALUE_NONE, 'Only show the current symlinked packages')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->showLinks($output);

        if ($input->getOption('show')) {
            return;
        }

        $source = realpath($input->getOption('directory'));
        if (!is_dir($source)) {
            throw new \InvalidArgumentException(sprintf('Source directory "%s" does not exist', $source === false ? $input->getOption('directory') : $source));
        }

        $reset = $input->getOption('reset');

        $packages = $input->getArgument('packages');
        $choices = $reset ? $this->getLinks() : $this->getPackages();

        if (empty($packages) && count($choices) === 1) {
            $packages = $choices;
        }

        if (empty($packages)) {
            if (empty($choices)) {
                $output->writeln('No packages available to '.($reset ? 'reset.' : 'symlink.'));

                return;
            }

            $output->writeln([
                'Select packages to '.($reset ? 'reset' : 'symlink.'),
                '',
                'Multiple choices are allowed, separate them with a comma, e.g. 0,2,3.',
            ]);

            $question = new ChoiceQuestion('', $choices);
            $question->setMultiselect(true);
            $packages = $this->getHelper('question')->ask($input, $output, $question);
        }

        $unknown = array_diff($packages, $choices);
        if (!empty($unknown)) {
            $msg = sprintf('Unknown %s "%s"', count($unknown) === 1 ? 'package' : 'packages', implode($unknown, '", "'));
            throw new \Exception($msg);
        }

        $reset ? $this->reset($output, $source, $packages) : $this->link($output, $source, $packages);

        $this->showLinks($output);
    }

    protected function showLinks(OutputInterface $output)
    {
        $output->writeln('');
        $links = array_fill_keys($this->getLinks(), true);
        $dirs = array_fill_keys($this->getPackages(), false);

        $lines = array_merge($links, $dirs);
        ksort($lines);

        $table = new Table($output);
        $table->setHeaders(['Directory', 'Symlink']);
        foreach ($lines as $package => $isLink) {
            $row = $isLink ? [null, $package] : [$package, null];
            $table->addRow($row);
        }

        $table->render();
        $output->writeln('');
    }

    protected function getLinks()
    {
        $links = Finder::create()
               ->directories()
               ->in('vendor/perform')
               ->filter(function ($file) {
                   return $file->isLink();
               })
               ->depth(0)
               ->getIterator();

        return array_map(function ($link) {
            return $link->getFileName();
        }, array_values(iterator_to_array($links)));
    }

    protected function getPackages()
    {
        $pkgs = Finder::create()
              ->directories()
              ->in('vendor/perform')
              ->filter(function ($file) {
                  return !$file->isLink();
              })
              ->depth(0)
              ->notName('_*')
              ->getIterator();

        return array_map(function ($pkg) {
            return $pkg->getFileName();
        }, array_values(iterator_to_array($pkgs)));
    }

    protected function link(OutputInterface $output, $performSource, array $packages)
    {
        foreach ($packages as $pkg) {
            $source = $this->getSource($performSource, $pkg);
            $target = 'vendor/perform/'.$pkg;
            $resetDir = 'vendor/perform/_'.$pkg;

            if (is_dir($target) && !is_link($target)) {
                rename($target, $resetDir);
            }
            @unlink($target);
            symlink($source, $target);
            $output->writeln(sprintf('Linked <info>%s</info> to <info>%s</info>', $source, $target));
        }
    }

    protected function reset(OutputInterface $output, $performSource, array $packages)
    {
        foreach ($packages as $pkg) {
            $source = $this->getSource($performSource, $pkg);
            $target = 'vendor/perform/'.$pkg;
            $resetDir = 'vendor/perform/_'.$pkg;

            if (!is_link($target)) {
                return;
            }
            if (!is_dir($resetDir)) {
                throw new \Exception(sprintf('Required directory %s was not found.', $resetDir));
            }
            unlink($target);
            rename($resetDir, $target);

            $output->writeln(sprintf('Restored vendor package <info>%s</info>', $target));
        }
    }

    protected function getSource($performSource, $pkg)
    {
        if ($pkg === 'perform-bundles') {
            return $performSource;
        }

        //transform foo-bar-bundle to FooBarBundle
        return $performSource.'/src/'.str_replace('-', '', ucwords($pkg, '-'));
    }
}
