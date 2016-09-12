<?php

namespace Perform\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

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
            ->setDescription('Symlink perform bundles in vendor/ to a local checkout')
            ->addOption('directory', '', InputOption::VALUE_REQUIRED,
                        'The location of the local checkout', '/home/vagrant/code/projects/admin-bundles/')
            ->addOption('reset', '', InputOption::VALUE_NONE, 'Remove the symlink')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $source = realpath($input->getOption('directory'));
        if (!is_dir($source)) {
            throw new \InvalidArgumentException(sprintf('Source directory "%s" does not exist', $source));
        }

        if ($input->getOption('reset')) {
            return $this->reset($output);
        }

        $target = './vendor/glynnforrest/admin-bundles';

        if (is_dir($target) && !is_link($target)) {
            rename($target, './vendor/glynnforrest/_admin-bundles');
        }
        @unlink($target);
        symlink($source, $target);
        $output->writeln(sprintf('Linked <info>%s</info> to <info>%s</info>', $source, $target));
    }

    protected function reset(OutputInterface $output)
    {
        $target = './vendor/glynnforrest/admin-bundles';
        $resetDir = './vendor/glynnforrest/_admin-bundles';

        if (!is_link($target)) {
            return;
        }
        if (!is_dir($resetDir)) {
            throw new \Exception(sprintf('Required directory <info>%s</info> was not found.', $resetDir));
        }
        unlink($target);
        rename($resetDir, $target);

        $output->writeln(sprintf('Restored vendor package <info>%s</info>', $target));
    }
}
