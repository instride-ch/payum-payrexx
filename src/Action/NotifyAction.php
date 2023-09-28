<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Wvision\Payum\Payrexx\Api;

class NotifyAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @inheritDoc
     *
     * @param Notify $request
     */
    public function execute($request): void
    {
        throw new HttpResponse('OK', 200, ['Content-Type' => 'text/plain', 'X-Notify-Message' => 'NO_TOKEN_HASH_FOUND']);
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof Notify
            && $request->getModel() instanceof \ArrayAccess;
    }
}
