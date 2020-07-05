<?php

namespace Bsi\Queue\Transport\Bitrix;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class MessageTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'bsi_queue_message';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),

            (new TextField('BODY'))
                ->configureRequired(true),

            (new ArrayField('HEADERS'))
                ->configureRequired(true)
                ->configureSerializationPhp(),

            (new StringField('QUEUE_NAME'))
                ->configureRequired(true)
                ->configureSize(190),

            (new DatetimeField('CREATED_AT'))
                ->configureRequired(true),

            (new DatetimeField('AVAILABLE_AT'))
                ->configureRequired(true),

            (new DatetimeField('DELIVERED_AT')),
        ];
    }
}
