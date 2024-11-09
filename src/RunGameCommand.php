<?php declare(strict_types = 1);

namespace Life;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function assert;
use function is_string;

final class RunGameCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('game:run');
        $this->setDescription('use input file [-i] and produce output file [-o]');
        $this->addOption('input', 'i', InputOption::VALUE_OPTIONAL, 'Input file', 'input.xml');
        $this->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.xml');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputFile = $input->getOption('input');
        assert(is_string($inputFile));
        $outputFile = $input->getOption('output');
        assert(is_string($outputFile));

        $game = new Game();
        $game->run($inputFile, $outputFile);

        $output->writeln('File ' . $outputFile . ' was saved.');

        return 0;
    }
}
