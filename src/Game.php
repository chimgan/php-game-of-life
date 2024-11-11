<?php declare(strict_types = 1);

namespace Life;

use InvalidArgumentException;
use Life\Interfaces\FileHandlerInterface;

/**
 * Class for handling game logic.
 *
 * This class is responsible for running the game, handling commands, and generating output in XML format.
 *
 * @package App\Game
 */
class Game
{
    private int $iterationsCount;

    private int $size;

    private int $species;

    private FileHandlerInterface $fileHandler;

    /**
     * @var int[][]|null[][]
     * Array of available cells in the game with size x size dimensions
     * Indexed by y coordinate and than x coordinate
     */
    private array $cells;

    public function __construct(FileHandlerInterface $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    /**
     * Run the game with the given parameters.
     *
     * The method processes the input parameters, launches the game and generates the result as an XML file.
     *
     * @param string $inputFile Path to the input file with the game data.
     * @param string $outputFile Path where the game result will be saved in XML format.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the input file is not found.
     */
    public function run(string $inputFile, string $outputFile): void
    {
        $fileData = $this->fileHandler->loadFile($inputFile);
        $this->size            = $fileData['worldSize'];
        $this->species         = $fileData['speciesCount'];
        $this->cells           = $fileData['cells'];
        $this->iterationsCount = $fileData['iterationsCount'];

        for ($i = 0; $i < $this->iterationsCount; $i++) {
            $newCells = [];
            for ($y = 0; $y < $this->size; $y++) {
                $newCells[] = [];
                for ($x = 0; $x < $this->size; $x++) {
                    $newCells[$y][$x] = $this->evolveCell($x, $y);
                }
            }
            $this->cells = $newCells;
        }

        $this->fileHandler->saveWorld($outputFile, $this->size, $this->species, $this->cells);
    }

    /**
     * Calculate the next state of the cell.
     *
     * @param int $x
     * @param int $y
     * @return int|null
     */
    private function evolveCell(int $x, int $y): ?int
    {
        $cell = $this->cells[$y][$x];
        $neighbours = $this->getNeighbours($x, $y);

        $sameSpeciesCount = $this->countSameSpecies($cell, $neighbours);
        if ($this->cellSurvives($cell, $sameSpeciesCount)) {
            return $cell;
        }

        $speciesForBirth = $this->evaluateSpeciesForBirth($neighbours);
        // Determine the species to be born if conditions for birth are met
        if (count($speciesForBirth) > 0) {
            // Randomly select one species from the viable species for birth
            return $speciesForBirth[array_rand($speciesForBirth)];
        }

        return null;
    }

    /**
     * Gather the list of neighbours for a cell.
     *
     * @param int $x
     * @param int $y
     * @return array
     */
    private function getNeighbours(int $x, int $y): array
    {
        $neighbours = [];

        for ($i = -1; $i <= 1; $i++) {
            for ($j = -1; $j <= 1; $j++) {
                if ($i === 0 && $j === 0) continue;
                $neighbourX = $x + $i;
                $neighbourY = $y + $j;

                if ($neighbourX >= 0 && $neighbourX < $this->size && $neighbourY >= 0 && $neighbourY < $this->size) {
                    $neighbours[] = $this->cells[$neighbourY][$neighbourX];
                }
            }
        }
        return $neighbours;
    }

    /**
     * Count the number of neighbour cells of the same species.
     *
     * @param int|null $cell
     * @param array $neighbours
     * @return int
     */
    private function countSameSpecies(?int $cell, array $neighbours): int
    {
        $sameSpeciesCount = 0;
        foreach ($neighbours as $neighbour) {
            if ($neighbour === $cell) {
                $sameSpeciesCount++;
            }
        }
        return $sameSpeciesCount;
    }

    /**
     * Determine if the cell survives to the next generation.
     *
     * @param int|null $cell
     * @param int $sameSpeciesCount
     * @return bool
     */
    private function cellSurvives(?int $cell, int $sameSpeciesCount): bool
    {
        return $cell !== null && $sameSpeciesCount >= 2 && $sameSpeciesCount <= 3;
    }

    /**
     * Evaluate which species can be born based on neighbour data.
     *
     * @param array $neighbours
     * @return array
     */
    private function evaluateSpeciesForBirth(array $neighbours): array
    {
        $speciesForBirth = [];
        for ($i = 0; $i < $this->species; $i++) {
            $oneSpeciesCount = 0;

            foreach ($neighbours as $neighbour) {
                if ($neighbour === $i) {
                    $oneSpeciesCount++;
                }
            }

            if ($oneSpeciesCount === 3) {
                $speciesForBirth[] = $i;
            }
        }
        return $speciesForBirth;
    }
}