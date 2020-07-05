<?php

namespace Bsi\Queue\Utils;

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bsi\Queue\Exception\InvalidArgumentException;
use Bsi\Queue\Exception\RuntimeException;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class WebpackEncoreLoader
{
    /** @var string */
    private $base;
    /** @var array */
    private $entryPoints;

    public function __construct(string $base = 'bitrix/js/bsi.queue')
    {
        $this->base = $base;
    }

    public function load(string $entryName): void
    {
        if ($this->entryPoints === null) {
            $this->loadEntryPoints();
        }

        if (!isset($this->entryPoints['entrypoints'][$entryName])) {
            throw new InvalidArgumentException(sprintf('Entry with name "%s" not found.', $entryName));
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
                    AssetLocation::BODY_END
                );
            }
        }
    }


    private function loadEntryPoints(): void
    {
        $entryPoints = json_decode(file_get_contents(
            realpath($_SERVER['DOCUMENT_ROOT'] . '/' . $this->base) . '/entrypoints.json'
        ), true);

        if ($entryPoints === false) {
            throw new RuntimeException('File "entrypoints.json" not found.');
        }

        $this->entryPoints = $entryPoints;
    }
}
