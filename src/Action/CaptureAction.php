<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use CoreShop\Bundle\PayumBundle\Request\GetStatus;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use Wvision\Payum\Payrexx\Api;
use Wvision\Payum\Payrexx\Request\GetHumanStatus;

class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param Capture $request
     */
    public function execute($request)
    {

        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->replace($this->api->executePayment($request));
        $this->gateway->execute(new GetHumanStatus($model));

        throw new HttpRedirect(
            $this->api->getAfterLink()
        );
    }

    public function supports($request)
    {
        return $request instanceof Capture && $request->getModel() instanceof \ArrayAccess;
    }
}
