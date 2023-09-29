<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Wvision\Payum\Payrexx\Api;
use Wvision\Payum\Payrexx\Request\Api\CaptureOffsite;
use Wvision\Payum\Payrexx\Request\Api\CreateTransaction;

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

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (array_key_exists('redirect_status', $httpRequest->query)) {
            $model->replace($httpRequest->query);
            return;
        }

        $transaction = new CreateTransaction($request->getToken());
        $transaction->setModel($model);

        $this->gateway->execute($transaction);
        $this->gateway->execute(new CaptureOffsite($model));
        //throw new HttpRedirect(
        //    $this->api->getAfterLink()
        //);
    }

    public function supports($request)
    {
        return $request instanceof Capture && $request->getModel() instanceof \ArrayAccess;
    }
}
