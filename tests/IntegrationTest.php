<?php declare(strict_types = 1);

namespace Tests\Life;

use DOMDocument;
use Life\Commands\RunGameCommand;
use Life\Handlers\XmlFileHandler;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class IntegrationTest extends TestCase
{
    private const OUTPUT_FILE = 'output.xml';
    private const FIXTURES_XML_PATH = __DIR__ . '/__fixtures__/';


    /**
     * Tests the execution of a game command, checking that the result matches the expected XML output.
     *
     * @dataProvider getInputAndExpectedOutputFiles
     */
    public function testGame(string $inputFile, string $expectedOutputFile): void
    {
        $fileHandler = new XmlFileHandler();
        $commandTester = new CommandTester(new RunGameCommand($fileHandler));

        $commandTester->execute(
            [
                '--input' => $inputFile,
                '--output' => self::OUTPUT_FILE,
            ]
        );

        $output = $this->loadXmlForComparison();

        Assert::assertXmlStringEqualsXmlFile(
            $expectedOutputFile,
            $output,
            'Expected XML and output XML should be same'
        );
    }

    /**
     * Get dataProvider for tests
     *
     * @return mixed
     */
    public function getInputAndExpectedOutputFiles(): array
    {
        $scenarios = range(1, 9);

        return array_map(
            static fn(int $scenario): array => [
                self::FIXTURES_XML_PATH . 'scenario-' . $scenario . '/input.xml',
                self::FIXTURES_XML_PATH . 'scenario-' . $scenario . '/output.xml',
            ],
            $scenarios
        );
    }

    /**
     * Load XML file for compare
     *
     * @return string
     */
    private function loadXmlForComparison(): string
    {
        if (!file_exists(self::OUTPUT_FILE)) {
            throw new \RuntimeException("Output file " . (self::OUTPUT_FILE) . " does not exist.");
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->load(self::OUTPUT_FILE);

        return $dom->saveXML();
    }

    /**
     * Clear test result data
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $outputFilePath = self::OUTPUT_FILE;
        if (file_exists($outputFilePath)) {
            unlink($outputFilePath);
        }
    }
}
