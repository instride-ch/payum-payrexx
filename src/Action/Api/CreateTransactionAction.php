<?php

/**
 * @author Miguel Gomes
 *
 * w-vision.
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright  Copyright (c) 2019 w-vision AG (https://www.w-vision.ch)
 */


declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action\Api;

use Payrexx\Models\Request\Transaction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Wvision\Payum\Payrexx\Api;
use Wvision\Payum\Payrexx\Request\Api\CreateTransaction;

class CreateTransactionAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param CreateTransaction $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());
        try {
            $returnUrl = $request->getToken()->getAfterUrl();
            $tokenHash = $request->getToken()->getHash();

            /** @var Transaction $gateway */
            $gateway = $this->api->createTransaction($request, $returnUrl, $tokenHash);
            $model->replace(['gateway_id' => $gateway->getId()]);
        } catch (\Throwable $e) {
            $model->replace(['error_message' => $e->getMessage(), 'failed' => true]);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof CreateTransaction &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
