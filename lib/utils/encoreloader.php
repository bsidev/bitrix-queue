<?php

namespace Bsi\Queue\Utils;

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bsi\Queue\Exception\RuntimeException;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class EncoreLoader
{
    /** @var string */
    private $base;
    /** @var string */
    private $entryPointsFile;
    /** @var array */
    private $entryPoints;

    public function __construct(string $base = 'bitrix/js/bsi.queue/', string $entryPointsFile = 'entrypoints.json')
    {
        $this->base = $base;
        $this->entryPointsFile = $entryPointsFile;

        $this->loadEntryPoints();
    }

    private function loadEntryPoints(): void
    {
        $entryPoints = json_decode(file_get_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/' . $this->base . $this->entryPointsFile
        ), true);

        if ($entryPoints === false) {
            throw new RuntimeException('`entrypoints.json` not found!');
        }

        $this->entryPoints = $entryPoints;
    }

    public function load(string $entryName): void
    {
        if (!isset($this->entryPoints['entrypoints'][$entryName])) {
            throw new RuntimeException('Entry `' . $entryName . '` not found in entrypoints file!');
        }

        $entry = $this->entryPoints['entrypoints'][$entryName];
        if (isset($entry['css']) && is_array($entry['css'])) {
            foreach ($entry['css'] as $path) {
                Asset::getInstance()->addString(
                    '<link rel="stylesheet" href="' . $path . '">',
                    true,
                    AssetLocation::AFTER_CSS
                );
            }
        }
        if (isset($entry['js']) && is_array($entry['js'])) {
            foreach ($entry['js'] as $path) {
                Asset::getInstance()->addString(
                    '<script src="' . $path . '"></script>',
                    true,
                    AssetLocation::AFTER_JS
                );
            }
        }
    }
}
