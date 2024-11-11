<?php declare(strict_types = 1);

namespace Life\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Life\Handlers\XmlFileHandler;
use Life\Game;

/**
 * Class Command for run application
 */
final class RunGameCommand extends Command
{
    /** @var string OPTION_INPUT */
    private const OPTION_INPUT = 'input';

    /** @var string OPTION_OUTPUT */
    private const OPTION_OUTPUT = 'output';

    /** @var XmlFileHandler $fileHandler XML facade DI purposes */
    private XmlFileHandler $fileHandler;

    public function __construct(XmlFileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
        parent::__construct();
    }

    /**
     * Configure game before run
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('game:run')
             ->setDescription('use input file [-i] and produce output file [-o]')
             ->addOption(self::OPTION_INPUT, 'i', InputOption::VALUE_OPTIONAL, 'Input file', 'input.xml')
             ->addOption(self::OPTION_OUTPUT, 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.xml');
    }

    /**
     * Executes the game run command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputFile = $input->getOption(self::OPTION_INPUT);
        assert(is_string($inputFile));
        $outputFile = $input->getOption(self::OPTION_OUTPUT);
        assert(is_string($outputFile));

        $game = new Game($this->fileHandler);

        try {
            $game->run($inputFile, $outputFile);
            $output->writeln('File ' . $outputFile . ' was saved.');
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}