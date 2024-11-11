<?php declare(strict_types = 1);

namespace Life\Configs;

/**
 * Config data for project
 *
 * @package Life\Configs
 */
class Config
{
    /** @var array|string[] $settings Config data */
    private static array $settings = [
        'project_root'           => __DIR__ . '/../..',
        'templates_path'         => __DIR__ . '/../../templates',
        'xml_game_template_name' => '/output-template.xml',
    ];

    /**
     * Getter for populate config data
     *
     * @param string $key
     * @return string|null
     */
   public static function get(string $key): string | null
   {
       return self::$settings[$key] ?? null;
   }
}
