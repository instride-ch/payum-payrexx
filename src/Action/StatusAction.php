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

namespace Wvision\Payum\Payrexx\Action;

use Payrexx\Models\Request\Transaction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Wvision\Payum\Payrexx\Api;
use Wvision\Payum\Payrexx\Request\GetHumanStatus;

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


        if (array_key_exists('gateway_id', $model)) {
            if ($model['gateway_id'] === null) {
                $request->markNew();
                return;
            }
        } else {
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
