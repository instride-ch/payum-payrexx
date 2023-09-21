<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Notify;
use Wvision\Payum\Payrexx\Api;
use Wvision\Payum\Payrexx\Request\GetHumanStatus;

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
        dd('notifyAction');
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute(new GetHumanStatus($request));
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
