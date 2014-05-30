<?php

namespace Visavi\Mirror\Command;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class MirrorCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('site:mirror')
            ->setDescription('Mirror a source directory to a destination directory updating only modified files.')
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'Origin directory'
            )
            ->addArgument(
                'destination',
                InputArgument::REQUIRED,
                'Destination directory'
            )
            ->addOption(
                'go',
                null,
                InputOption::VALUE_NONE,
                'Actually make the copy, just simulate it if not specified.'
            )
            ->setHelp(<<<EOT
The <info>site:mirror</info> mirror a source directory to a destination directory updating only modified files. 
It compare the content of each file of the source dir with the files in the destination dir and update the 
destination if needed. At the end if remove all files in the destination dir that is not in the source dir.

  <info>php bin/console site:mirror /path/to/source/dir /path/to/destination/dir</info>

This command just simulate the mirroring, if you want to actualy do it add the <info>--go</info> option.

  <info>php bin/console site:mirror --go /path/to/source/dir /path/to/destination/dir</info>

EOT
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = rtrim($input->getArgument('source'),'/').'/';
        $dest = rtrim($input->getArgument('destination'),'/').'/';
        $go = $input->getOption('go');
        $output->writeln('<comment>Mirroring '.$source.' to '.$dest.'</comment>');
        
        if (!is_dir($source)) {
            throw new IOException('source directory not found');
        }
        if (!is_dir($dest)) {
            throw new IOException('destination directory not found');
        }
        if (!is_writable($dest)) {
            throw new IOException('destination directory not writable');
        }
        $nothing = true;

        $finder = new Finder();
        $fs = new Filesystem();
        $finder->files()->in($source);
        if ($go) {
            $action = '<comment>copy: </comment>';
        } else {
            $action = '<comment>will copy: </comment>';
        }
        foreach ($finder as $file) {
            $do_copy = false;
            $file_out = $dest.$file->getRelativePathname();
            if (!file_exists($file_out) || (md5_file($file_out) != md5_file($file->getRealpath()))) {
                if ($go) {
                    $fs->copy($file->getRealpath(), $file_out);
                }
                $output->writeln($action.$file->getRealpath() .' to '.$file_out);
                $nothing = false;
            }
        }
        // now delete files not in sources
        $finder = new Finder();
        $finder->files()->in($dest);
        if ($go) {
            $action = '<comment>remove: </comment>';
        } else {
            $action = '<comment>will remove: </comment>';
        }
        foreach ($finder as $file) {
            $file_out = $source.$file->getRelativePathname();
            $path = $file->getRealpath();
            if (!file_exists($file_out)) {
                if ($go) {
                    $fs->remove($path);
                }
                $output->writeln($action.$path);
                $nothing = false;
            }
        }
        if ($nothing) {
            $output->writeln('<comment>Destination directory is uptodate!</comment>');
        }
    }
}