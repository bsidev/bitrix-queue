<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bsi\Queue\Utils\WebpackEncoreLoader;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

Loc::loadMessages(__FILE__);
Loader::includeModule('bsi.queue');

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

$APPLICATION->SetTitle(Loc::getMessage('BSI_QUEUE_ADMIN_DASHBOARD_TITLE'));

(new WebpackEncoreLoader())->load('app');

echo '<div class="vue-shell" data-name="Dashboard"></div>';

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
