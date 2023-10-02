<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payrexx\Models\Request\Transaction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;
use Wvision\Payum\Payrexx\Api;


class SyncAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param Sync $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->wh_log('sync action');

        $model = $request->getModel();

        if ($model['gateway_id'] === null) {
            return;
        }

        $this->wh_log(json_decode($model).' - gateway model');
//        $this->wh_log($model['gateway_id'].' - gateway id model');
//        $this->wh_log($request->getFirstModel()['gateway_id'].' - gateway id firstmodel');

        $gateway = $this->api->getPayrexxGateway($model['gateway_id']);
        $transaction = $this->api->getTransactionByGateway($gateway);

        if (!$transaction instanceof Transaction) {
            $this->wh_log('transaction not Transaction');
            return;
        }
        $this->wh_log('transaction found!');

        $model->replace($this->api->createTransactionInfo($transaction));
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

    public function supports($request): bool
    {
        $this->wh_log('sync action supports '.get_class($request));
        return
            $request instanceof Sync;
    }
}
