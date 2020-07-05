<?php

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

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

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    public function installFiles(): bool
    {
        return true;
    }

    public function uninstallFiles(): bool
    {
        return true;
    }
}
