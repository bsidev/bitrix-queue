<?php

namespace Bsi\Queue\Monitoring\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Request;
use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Bsi\Queue\Queue;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
abstract class AbstractController extends Controller
{
    /** @var AdapterInterface */
    protected $adapter;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->adapter = Queue::getInstance()->getContainer()->get(AdapterInterface::class);
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }
}
