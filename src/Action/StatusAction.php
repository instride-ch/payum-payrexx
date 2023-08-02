<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payrexx\Models\Response\Transaction;
use Payrexx\Payrexx;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Wvision\Payum\Payrexx\Request\GetHumanStatus;

class StatusAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Payrexx::class;
    }

    /**
     * @inheritDoc
     *
     * @param GetHumanStatus $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());

        if (null === $model['pfc_transaction_id']) {
            $request->markNew();

            return;
        }

        $transaction = $this->api->getApi()->getOne($model['pfc_transaction_id']);
        $status = $transaction->getStatus();

        switch ($status) {
            case Transaction::CONFIRMED:
                if ($request instanceof GetHumanStatus) {
                    $request->markConfirmed();
                }

                break;
//            case Transaction::COMPLETED:
//            case Transaction::FULFILL:
//                $request->markCaptured();
//
//                break;
            case Transaction::WAITING:
                $request->markPending();

                break;
            case Transaction::AUTHORIZED:
                $request->markAuthorized();

                break;
//            case Transaction::DECLINE:
//            case Transaction::FAILED:
//                $request->markFailed();
//
//                break;
            default:
                $request->markUnknown();

                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess;
    }
}
