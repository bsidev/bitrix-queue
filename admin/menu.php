<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!$USER->IsAdmin()) {
    return false;
}

if (!Loader::includeModule('bsi.queue')) {
    return false;
}

return [
    'parent_menu' => 'global_menu_settings',
    'section' => 'bsi_queue',
    'sort' => 2000,
    'text' => Loc::getMessage('BSI_QUEUE_MENU_TEXT'),
    'title' => Loc::getMessage('BSI_QUEUE_MENU_TITLE'),
    'icon' => 'bsi_queue_menu_icon',
    'page_icon' => 'bsi_queue_page_icon',
    'items_id' => 'menu_bsi_queue',
    'items' => [
        [
            'text' => Loc::getMessage('BSI_QUEUE_MENU_DASHBOARD_TEXT'),
            'title' => Loc::getMessage('BSI_QUEUE_MENU_DASHBOARD_TITLE'),
            'url' => 'bsi_queue_dashboard.php?lang=' . LANGUAGE_ID,
            'more_url' => ['bsi_queue_dashboard.php'],
        ],
    ],
];
