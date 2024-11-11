<?php declare(strict_types = 1);

namespace Life\Handlers;

use InvalidArgumentException;
use RuntimeException;
use SimpleXMLElement;
use Life\Exceptions\InvalidInputException;

/**
 * XML reader class
 *
 * @package Life\Handlers
 */
class XmlFileReader
{
    public function __construct(private readonly string $filePath)
    {
    }

    /**
     * @throws InvalidInputException
     */
    public function loadFile(): array
    {
        $life = $this->loadXmlFile();
        $this->validateXmlFile($life);

        $worldSize       = $this->validateWorldSize($life);
        $speciesCount    = $this->validateSpeciesCount($life);
        $iterationsCount = $this->validateIterationsCount($life);

        $cells = $this->readCells($life, $worldSize, $speciesCount);

        return [
            'worldSize'       => $worldSize,
            'speciesCount'    => $speciesCount,
            'cells'           => $cells,
            'iterationsCount' => $iterationsCount
        ];
    }

    /**
     * Validate iteration count
     *
     * @param SimpleXMLElement $life
     * @return int
     * @throws InvalidInputException
     */
    public function validateIterationsCount(SimpleXMLElement $life): int
    {
        $iterationsCount = (int)$life->world->iterations;
        if ($iterationsCount < 0) {
            throw new InvalidInputException("Value of element 'iterations' must be zero or positive number");
        }
        return $iterationsCount;
    }

    /**
     * Validate world size
     *
     * @param SimpleXMLElement $life
     * @return int
     * @throws InvalidInputException
     */
    public function validateWorldSize(SimpleXMLElement $life): int
    {
        $worldSize = (int)$life->world->cells;
        if ($worldSize <= 0) {
            throw new InvalidInputException("Value of element 'cells' must be positive number");
        }
        return $worldSize;
    }

    /**
     * Validate Species Count
     *
     * @param SimpleXMLElement $life
     * @return int
     * @throws InvalidInputException
     */
    public function validateSpeciesCount(SimpleXMLElement $life): int
    {
        $speciesCount = (int)$life->world->species;
        if ($speciesCount <= 0) {
            throw new InvalidInputException("Value of element 'species' must be positive number");
        }
        return $speciesCount;
    }


    /**
     * Load XML File
     *
     * @return SimpleXMLElement
     */
    private function loadXmlFile(): SimpleXMLElement
    {
        $this->ensureFileExists($this->filePath);
        $xmlContent = $this->readFileContents($this->filePath);
        return $this->parseXmlContent($xmlContent);
    }

    /**
     * Ensures that the given file exists.
     *
     * @param string $filePath
     * @return void
     */
    private function ensureFileExists(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File does not exist: {$filePath}");
        }
    }

    /**
     * Reads the contents of the given file.
     *
     * @param string $filePath
     * @return string
     */
    private function readFileContents(string $filePath): string
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException("Failed to read file: {$filePath}");
        }
        return $content;
    }

    /**
     * Parses the XML content and returns a SimpleXMLElement.
     *
     * @param string $xmlContent
     * @return SimpleXMLElement
     */
    private function parseXmlContent(string $xmlContent): SimpleXMLElement
    {
        libxml_use_internal_errors(true);
        $life = simplexml_load_string($xmlContent, SimpleXMLElement::class);

        if ($life === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new InvalidArgumentException("Failed to parse XML: " . print_r($errors, true));
        }

        return $life;
    }


    /**
     * Validate XML file
     *
     * @param SimpleXMLElement $life
     * @return void
     * @throws InvalidInputException
     */
    private function validateXmlFile(SimpleXMLElement $life): void
    {
        $requiredElements = [
            'world' => ['iterations', 'cells', 'species'],
            'organisms' => ['organism' => ['x_pos']],
        ];

        foreach ($requiredElements as $mainElement => $subElements) {
            if (!isset($life->$mainElement)) {
                throw new InvalidInputException("Missing element '$mainElement'");
            }

            foreach ($subElements as $key => $subElement) {
                if (is_array($subElement)) {
                    foreach ($life->$mainElement->$key as $element) {
                        foreach ($subElement as $item) {
                            if (!isset($element->$item)) {
                                throw new InvalidInputException("Missing element '$item' in some of the element '$key'");
                            }
                        }
                    }
                } else {
                    if (!isset($life->$mainElement->$subElement)) {
                        throw new InvalidInputException("Missing element '$subElement'");
                    }
                }
            }
        }
    }


    /**
     * Read cells
     *
     * @param SimpleXMLElement $life
     * @param int $worldSize
     * @param int $speciesCount
     * @return array
     * @throws InvalidInputException
     */
    private function readCells(SimpleXMLElement $life, int $worldSize, int $speciesCount): array
    {
        $cells = [];
        foreach ($life->organisms->organism as $organism) {
            list($x, $y, $species) = $this->extractAndValidateOrganism($organism, $worldSize, $speciesCount);
            $cells[$y][$x] = $cells[$y][$x] ?? $this->resolveSpeciesConflict($cells, $x, $y, $species);
        }
        return $this->initializeCellGrid($cells, $worldSize);
    }

    /**
     * Extracts and validates organism data from XML.
     *
     * @param SimpleXMLElement $organism
     * @param int $worldSize
     * @param int $speciesCount
     * @return array
     * @throws InvalidInputException
     */
    private function extractAndValidateOrganism(SimpleXMLElement $organism, int $worldSize, int $speciesCount): array
    {
        $x = $this->validateCoordinate((int)$organism->x_pos, $worldSize, 'x_pos');
        $y = $this->validateCoordinate((int)$organism->y_pos, $worldSize, 'y_pos');
        $species = $this->validateCoordinate((int)$organism->species, $speciesCount, 'species');
        return [$x, $y, $species];
    }

    /**
     * Validates if a coordinate falls within the allowed range.
     *
     * @param int $value
     * @param int $max
     * @param string $field
     * @return int
     * @throws InvalidInputException
     */
    private function validateCoordinate(int $value, int $max, string $field): int
    {
        if ($value < 0 || $value >= $max) {
            throw new InvalidInputException("Value of element '$field' must be between 0 and the maximum limit");
        }
        return $value;
    }

    /**
     * Resolves species conflicts at a specific cell position.
     *
     * @param array $cells
     * @param int $x
     * @param int $y
     * @param int $species
     * @return int
     */
    private function resolveSpeciesConflict(array &$cells, int $x, int $y, int $species): int
    {
        if (isset($cells[$y][$x])) {
            $existingSpecies = $cells[$y][$x]; /** @var int $existingSpecies */
            $availableSpecies = [$existingSpecies, $species];
            return $availableSpecies[array_rand($availableSpecies)];
        }
        return $species;
    }

    /**
     * Ensures all cell positions are initialized.
     *
     * @param array $cells
     * @param int $worldSize
     * @return array
     */
    private function initializeCellGrid(array $cells, int $worldSize): array
    {
        for ($y = 0; $y < $worldSize; $y++) {
            $cells[$y] ??= [];
            for ($x = 0; $x < $worldSize; $x++) {
                $cells[$y][$x] = $cells[$y][$x] ?? null;
            }
        }
        return $cells;
    }
}
