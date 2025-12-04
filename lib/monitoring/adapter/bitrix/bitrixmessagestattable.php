<?php

namespace Bsi\Queue\Monitoring\Adapter\Bitrix;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixMessageStatTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'bsi_queue_message_stat';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),

            (new StringField('UUID'))
                ->configureRequired(true)
                ->configureUnique(true),

            (new StringField('MESSAGE'))
                ->configureRequired(true),

            (new StringField('STATUS'))
                ->configureRequired(true),

            (new TextField('BODY'))
                ->configureRequired(true),

            (new ArrayField('HEADERS'))
                ->configureSerializationPhp(),

            (new StringField('TRANSPORT_NAME'))
                ->configureSize(190),

            (new TextField('ERROR')),

            (new DatetimeField('SENT_AT'))
                ->configureRequired(true)
                ->configureDefaultValue(static function () {
                    return new DateTime();
                }),

            (new DatetimeField('RECEIVED_AT')),

            (new DatetimeField('HANDLED_AT')),

            (new DatetimeField('FAILED_AT')),
        ];
    }

    public static function getRowByUuid(string $uuid, array $select = ['*']): ?array
    {
        return static::getRow([
            'select' => $select,
            'filter' => ['=UUID' => $uuid],
        ]);
    }
}
