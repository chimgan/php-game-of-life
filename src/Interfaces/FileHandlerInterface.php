<?php declare(strict_types = 1);

namespace Life\Interfaces;

interface FileHandlerInterface
{
   public function loadFile(string $inputFile): array;
   public function saveWorld(string $outputFile, int $size, int $species, array $cells): void;
}