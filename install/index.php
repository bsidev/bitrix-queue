<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bsi\Queue\Monitoring\Agent\CleanUpStatsAgent;

class bsi_queue extends CModule
{
    public const MODULE_ID = 'bsi.queue';

    public $MODULE_ID = 'bsi.queue';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $PARTNER_NAME = 'Sergey Balasov';
    public $PARTNER_URI = 'https://bsidev.ru';
    public $MODULE_DESCRIPTION;
    public $errors = false;

    public function __construct()
    {
        $arModuleVersion = [];

        Loc::loadMessages(__FILE__);

        include __DIR__ . '/version.php';
        if (is_array($arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'] ?? null;
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'] ?? null;
        }

        $this->MODULE_NAME = Loc::getMessage('BSI_QUEUE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('BSI_QUEUE_MODULE_DESCRIPTION');
    }

    public function doInstall(): void
    {
        global $APPLICATION;

        $this->installFiles();
        $this->installDb();

        Loader::includeModule($this->MODULE_ID);

        $APPLICATION->includeAdminFile(
            Loc::getMessage('BSI_QUEUE_INSTALL_TITLE'),
            __DIR__ . '/step1.php'
        );
    }

    public function doUninstall(): void
    {
        global $APPLICATION, $step;

        $step = (int) $step;
        if ($step < 2) {
            $GLOBALS['errors'] = [];
            $APPLICATION->includeAdminFile(
                Loc::getMessage('BSI_QUEUE_UNINSTALL_TITLE'),
                __DIR__ . '/unstep1.php'
            );
        } elseif ($step === 2) {
            $this->uninstallDb([
                'savedata' => $_REQUEST['savedata'],
            ]);
            $this->uninstallFiles();

            $GLOBALS['errors'] = [];
            $APPLICATION->includeAdminFile(
                Loc::getMessage('BSI_QUEUE_UNINSTALL_TITLE'),
                __DIR__ . '/unstep2.php'
            );
        }
    }

    public function installDb(): bool
    {
        global $APPLICATION, $DB;

        $this->errors = $DB->RunSQLBatch(__DIR__ . '/db/' . strtolower($DB->type) . '/install.sql');
        if (!empty($this->errors)) {
            $APPLICATION->ThrowException(implode('', $this->errors));

            return false;
        }

        ModuleManager::registerModule($this->MODULE_ID);

        $startTime = ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 60, 'FULL');
        CAgent::AddAgent(
            CleanUpStatsAgent::class . '::run();',
            $this->MODULE_ID,
            'N',
            86400,
            '',
            'Y',
            $startTime,
            100,
            false,
            false
        );

        return true;
    }

    public function uninstallDb($params = []): bool
    {
        global $APPLICATION, $DB;

        $this->errors = false;
        if (!$params['savedata']) {
            $this->errors = $DB->RunSQLBatch(__DIR__ . '/db/' . strtolower($DB->type) . '/uninstall.sql');
        }

        if (!empty($this->errors)) {
            $APPLICATION->ThrowException(implode('', $this->errors));

            return false;
        }

        if (!$params['savedata']) {
            Option::delete($this->MODULE_ID);
        }

        CAgent::RemoveModuleAgents($this->MODULE_ID);

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    public function installFiles(): bool
    {
        CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        CopyDirFiles(__DIR__ . '/js', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js', true, true);
        CopyDirFiles(__DIR__ . '/themes', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes', true, true);

        return true;
    }

    public function uninstallFiles(): bool
    {
        DeleteDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFiles(__DIR__ . '/js', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js');
        DeleteDirFiles(__DIR__ . '/themes/.default/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/');
        DeleteDirFilesEx('/bitrix/themes/.default/icons/bsi.queue/');

        return true;
    }
}
