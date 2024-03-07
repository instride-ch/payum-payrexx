<?php

/**
 * @author Miguel Gomes
 *
 * instride AG
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright 2024 instride AG (https://instride.ch)
 */

declare(strict_types=1);

namespace Instride\Payum\Payrexx\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Instride\Payum\Payrexx\Request\Api\CaptureOffsite;
use Instride\Payum\Payrexx\Request\Api\CreateTransaction;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

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
    }

    public function supports($request)
    {
        return $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess;
    }
}
