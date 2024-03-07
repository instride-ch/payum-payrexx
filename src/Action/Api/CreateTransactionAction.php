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

namespace Instride\Payum\Payrexx\Action\Api;

use Payrexx\Models\Request\Transaction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Instride\Payum\Payrexx\Api;
use Instride\Payum\Payrexx\Request\Api\CreateTransaction;

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
