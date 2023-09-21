<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

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
        if (empty($model)) {
            $request->markNew();
        }
//        if ($request instanceof GetHumanStatus) {
//            dd($model['status']);
//        }

        switch ($model['status']) {
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
