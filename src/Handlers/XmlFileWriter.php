<?php declare(strict_types = 1);

namespace Life\Handlers;

use Life\Configs\Config;
use SimpleXMLElement;
use Life\Exceptions\OutputWritingException;

/**
 * Class XmlFileWriter
 * Handles writing world data to an XML file.
 *
 * @package Life\Handlers
 */
class XmlFileWriter
{
    /** @var string $templatePath */
    private string $templatePath;

    /**
     * XmlFileWriter constructor.
     *
     * @param string $filePath Path where the XML will be saved.
     */
    public function __construct(private readonly string $filePath)
    {
        $templatePath = Config::get('templates_path');
        $templateName = Config::get('xml_game_template_name');
        $this->templatePath = $templatePath . $templateName;
    }

    /**
     * Save the world data to an XML file.
     *
     * @param int $worldSize Size of the world.
     * @param int $speciesCount Number of species.
     * @param array $cells 2D array representing the world.
     * @throws OutputWritingException
     */
    public function saveWorld(int $worldSize, int $speciesCount, array $cells): void
    {
        $life = simplexml_load_string(file_get_contents($this->templatePath));
        $life->world->cells = $worldSize;
        $life->world->species = $speciesCount;

        $this->addOrganisms($life, $worldSize, $cells);

        $this->saveXml($life);
    }

    /**
     * Add organisms to the XML structure.
     *
     * @param SimpleXMLElement $life XML element to add organisms to.
     * @param int $worldSize Size of the world.
     * @param array $cells 2D array representing the world.
     */
    private function addOrganisms(SimpleXMLElement $life, int $worldSize, array $cells): void
    {
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
    }

    /**
     * Save the XML element to a file.
     *
     * @param SimpleXMLElement $life XML element to save.
     * @throws OutputWritingException
     */
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
