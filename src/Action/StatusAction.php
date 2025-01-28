<?php

declare(strict_types=1);

/**
 * instride AG
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright 2024 instride AG (https://instride.ch)
 */

namespace Instride\Payum\Payrexx\Action;

use Instride\Payum\Payrexx\Api;
use Instride\Payum\Payrexx\Request\GetHumanStatus;
use Payrexx\Models\Request\Transaction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = $request->getModel();

        if (! isset($model['gateway_id'])) {
            $request->markNew();

            return;
        }

        $gateway = $this->api->getPayrexxGateway($model['gateway_id']);
        $transaction = $this->api->getTransactionByGateway($gateway);

        if (!$transaction instanceof Transaction) {
            $request->markUnknown();

            return;
        }

        $state = $transaction->getStatus();

        switch ($state) {
            case GetHumanStatus::STATUS_CAPTURED:
            case GetHumanStatus::STATUS_CONFIRMED:
                $request->markCaptured();
                break;
            case GetHumanStatus::STATUS_PENDING:
            case GetHumanStatus::STATUS_WAITING:
                $request->markPending();
                break;
            case GetHumanStatus::STATUS_FAILED:
                $request->markFailed();
                break;
            case GetHumanStatus::STATUS_AUTHORIZED:
                $request->markAuthorized();
                break;
            case GetHumanStatus::STATUS_PAYEDOUT:
                $request->markPayedout();
                break;
            case GetHumanStatus::STATUS_CANCELED:
                $request->markCanceled();
                break;
        }
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface;
    }
}
