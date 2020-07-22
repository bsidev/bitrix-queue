<?php

/** @noinspection PhpDynamicAsStaticMethodCallInspection */

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/options.php');
Loc::loadMessages(__FILE__);

$module_id = 'bsi.queue';
Loader::includeModule($module_id);

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$request = Application::getInstance()->getContext()->getRequest();

$tabs = [
    [
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('MAIN_TAB_SET'),
        'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_SET'),
        'ICON' => '',
    ],
];
$tabControl = new CAdminTabControl('tabControl', $tabs);

$allOptions = [
    [
        'id' => 'stats_lifetime',
        'name' => Loc::getMessage('BSI_QUEUE_OPTION_STATS_LIFETIME'),
        'type' => 'integer',
    ],
];

foreach ($allOptions as &$option) {
    $option['value'] = Option::get($module_id, $option['id'], null);
}
unset($option);

$errorMessage = '';
if ($request->isPost() && strlen($request['Update']) > 0 && check_bitrix_sessid()) {
    foreach ($allOptions as $option) {
        $value = $request[$option['id']] ?? null;

        if ($option['type'] === 'integer') {
            $value = (int) $value;
        } elseif (!is_string($value)) {
            $value = trim($value);
        }

        Option::set($mid, $option['id'], $value);
    }

    if ($e = $APPLICATION->getException()) {
        CAdminMessage::ShowMessage([
            'DETAILS' => $e->getString(),
            'TYPE' => 'ERROR',
            'HTML' => true,
        ]);
    } elseif ($request['back_url_settings'] !== '') {
        LocalRedirect($request['back_url_settings']);
    } else {
        LocalRedirect($APPLICATION->GetCurPage() . '?' . http_build_query([
                'mid' => $mid,
                'lang' => LANGUAGE_ID,
                'back_url_settings' => $request['back_url_settings'],
            ]) . '&' . $tabControl->ActiveTabParam());
    }
}
?>
<form method="post"
      action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&lang=<?= LANGUAGE_ID ?>">
    <?php
    echo bitrix_sessid_post();
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    if ($errorMessage) : ?>
        <tr>
            <td colspan="2" style="text-align: center;"><b style="color: red;"><?= $errorMessage ?></b></td>
        </tr>
    <?php endif ?>
    <?php foreach ($allOptions as $option) : ?>
        <tr>
            <td style="width: 40%;"><?= $option['name'] ?>:</td>
            <td style="width: 60%;">
                <input type="text" name="<?= $option['id'] ?>" value="<?= htmlspecialchars($option['value']) ?>">
            </td>
        </tr>
    <?php endforeach ?>
    <?php
    $tabControl->Buttons() ?>
    <input type="submit" name="Update" value="<?= Loc::getMessage('MAIN_SAVE') ?>" class="adm-btn-save">
    <input type="reset" name="reset" value="<?= Loc::getMessage('MAIN_RESET') ?>">
    <?php $tabControl->End() ?>
</form>