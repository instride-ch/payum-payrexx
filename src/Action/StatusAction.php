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

    private function wh_log($log_msg)
    {
        $log_filename = $_SERVER['DOCUMENT_ROOT'] . "/log_test";
        if (!file_exists($log_filename)) {
            // create directory/folder uploads.
            mkdir($log_filename, 0777, true);
        }
        $log_file_data = $log_filename . '/log_' . date('d-M-Y:h:i:s') . '.log';
        file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
    }

    /**
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        $this->wh_log('status Action');
        $model = $request->getModel();
        if ($model['gateway_id'] === null) {
            $request->markNew();

            return;
        }

        //       $transaction->setId(($model['transaction_id']));
        $gateway = $this->api->getPayrexxGateway($model['gateway_id']);
        $transaction = $this->api->getTransactionByGateway($gateway);
        if (!$transaction instanceof Transaction) {
            $request->markUnknown();

            return;
        }
        $state = $transaction->getStatus();

        switch ($state) {
            case GetHumanStatus::STATUS_CONFIRMED:
                if ($request instanceof GetHumanStatus) {
                    $request->markConfirmed();
                }
                break;
            case GetHumanStatus::STATUS_WAITING:
                if ($request instanceof GetHumanStatus) {
                    $request->markWaiting();
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
        $this->wh_log('status Action supports? - '.get_class($request));

        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
