<?php

namespace Bsi\Queue\Monitoring;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
final class MessageStatuses
{
    public const SENT = 'sent';
    public const RECEIVED = 'received';
    public const HANDLED = 'handled';
    public const FAILED = 'failed';
}
