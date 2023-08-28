<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payrexx\Payrexx;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Wvision\Payum\Payrexx\Request\GetHumanStatus;

class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = Payrexx::class;
    }

    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        $model = $request->getModel();

        $model->replace($this->api->executePayment());
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new GetHumanStatus($model));
    }

    public function supports($request)
    {
        return $request instanceof Capture && $request->getModel() instanceof \ArrayAccess;
    }
}
