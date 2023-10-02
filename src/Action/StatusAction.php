<?php

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
        $this->wh_log('status Action model start');
        RequestNotSupportedException::assertSupports($this, $request);
        try {
            $model = $request->getModel();
            $this->wh_log('status Action model - asserts -');

        } catch (\Exception $e) {
            $this->wh_log($e->getMessage().' '. $e->getLine());
        }

        if ($request instanceof GetHumanStatus) {
            $transaction = $request->getTransaction();
        }

        if (array_key_exists('gateway_id', $model) ) {
            if ($model['gateway_id'] === null) {
                $request->markNew();
                return;
            }
        } else {
            $this->wh_log('No Gateway Id given');
            $request->markNew();
            return;
        }

        $this->wh_log('Expected ');
        $this->wh_log($model['gateway_id']);

        $gateway = $this->api->getPayrexxGateway($model['gateway_id']);
        $transaction = $this->api->getTransactionByGateway($gateway);

        $this->wh_log('Transaction -> ' . get_class($transaction));
        if (!$transaction instanceof Transaction) {
            $request->markUnknown();

            return;
        }
        $state = $transaction->getStatus();
        $this->wh_log($state);
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
        $this->wh_log('status Action supports? - ' . get_class($request));

        return $request instanceof GetStatusInterface;
    }
}
