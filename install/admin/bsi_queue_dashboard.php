<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bsi.queue/admin/dashboard.php')) {
    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bsi.queue/admin/dashboard.php');
} else {
    require($_SERVER['DOCUMENT_ROOT'] . '/local/modules/bsi.queue/admin/dashboard.php');
}
