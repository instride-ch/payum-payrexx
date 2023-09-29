<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payrexx\Models\Request\Transaction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
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
        $model = $request->getModel();
        if ($model['transaction_id'] === null) {
            $request->markNew();

            return;
        }
        $transaction = new \Payrexx\Models\Request\Transaction();
        $transaction->setId(($model['transaction_id']));
        $transaction = $this->api->getApi()->getOne($transaction);
        if (!$transaction instanceof Transaction) {
            $request->markUnknown();

            return;
        }
        dd($transaction);
        $state = $transaction->getResponseModel()->getStatus();

        switch ($state) {
            case GetHumanStatus::STATUS_CONFIRMED:
                if ($request instanceof GetHumanStatus) {
                    $request->markConfirmed();
                }
                break;
            case GetHumanStatus::STATUS_CAPTURED:
                $request->markCaptured();
                break;
            case GetHumanStatus::STATUS_FAILED:
                $request->markFailed();
                break;
            case GetHumanStatus::STATUS_AUTHORIZED:
                $request->markAuthorized();
                break;
            case GetHumanStatus::STATUS_PENDING:
                $request->markPending();
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
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
