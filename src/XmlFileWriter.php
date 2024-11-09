<?php declare(strict_types = 1);

namespace Life;

use SimpleXMLElement;

class XmlFileWriter
{
    private const OUTPUT_TEMPLATE = '/output-template.xml';


    public function __construct(private readonly string $filePath)
    {
    }


    public function saveWorld(int $worldSize, int $speciesCount, array $cells): void
    {
        $life = simplexml_load_string(file_get_contents(__DIR__ . self::OUTPUT_TEMPLATE));
        $life->world->cells = $worldSize;
        $life->world->species = $speciesCount;
        for ($y = 0; $y < $worldSize; $y++) {
            for ($x = 0; $x < $worldSize; $x++) {
                $cell = $cells[$y][$x]; /** @var int|null $cell */
                if ($cell !== null) {
                    $organism = $life->organisms->addChild('organism');
                    /** @var SimpleXMLElement $organism */
                    $organism->addChild('x_pos', (string)$x);
                    $organism->addChild('y_pos', (string)$y);
                    $organism->addChild('species', (string)$cell);
                }
            }
        }
        $this->saveXml($life);
    }


    private function saveXml(SimpleXMLElement $life): void
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($life->asXML());
        $result = file_put_contents($this->filePath, $dom->saveXML());
        if ($result === false) {
            throw new OutputWritingException("Writing XML file failed");
        }
    }
}
