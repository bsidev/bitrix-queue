<?php

namespace Bsi\Queue\Monitoring\Entity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\Type\DateTime;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class StatTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'bsi_queue_stat';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),

            (new StringField('UID'))
                ->configureRequired(true)
                ->configureUnique(true),

            (new StringField('MESSAGE'))
                ->configureRequired(true),

            (new StringField('STATUS'))
                ->configureRequired(true),

            (new StringField('TRANSPORT')),

            (new ArrayField('BUSES'))
                ->configureSerializationPhp(),

            (new DatetimeField('CREATED_AT'))
                ->configureRequired(true)
                ->configureDefaultValue(function () {
                    return new DateTime();
                }),

            (new DatetimeField('RECEIVED_AT')),

            (new DatetimeField('HANDLED_AT')),

            (new DatetimeField('FAILED_AT')),
        ];
    }
}
