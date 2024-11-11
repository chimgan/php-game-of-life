<?php declare(strict_types = 1);

namespace Life\Handlers;

use Life\Exceptions\InvalidInputException;
use Life\Exceptions\OutputWritingException;
use Life\Interfaces\FileHandlerInterface;

/**
 * Facade for simple use XNL functionality
 *
 * @package Life\Handlers
 */
class XmlFileHandler implements FileHandlerInterface
{
    /**
     * Load data from XML file
     *
     * @throws InvalidInputException
     */
    public function loadFile(string $inputFile): array
    {
        $reader = new XmlFileReader($inputFile);
        return $reader->loadFile();
    }

    /**
     * Save result data to XML file
     *
     * @throws OutputWritingException
     */
    public function saveWorld(string $outputFile, int $size, int $species, array $cells): void
    {
        $writer = new XmlFileWriter($outputFile);
        $writer->saveWorld($size, $species, $cells);
    }
}
