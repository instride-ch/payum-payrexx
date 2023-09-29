<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payrexx\Models\Request\Transaction;
use Wvision\Payum\Payrexx\Api;
use Exception;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\Base;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;

class NotifyNullAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

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
     * @inheritDoc
     *
     * @param Notify $request
     */
    public function execute($request): void
    {

        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        try {
            $parsedBody = \json_decode($httpRequest->content, true, 512, JSON_THROW_ON_ERROR);

            if ($parsedBody === false) {
                return;
            }

            $transaction = new \Payrexx\Models\Request\Transaction();
            $id = $parsedBody['transaction']['id'];
            $transaction->setId($id);

            try {
                $transaction = $this->api->getApi()->getOne($transaction);
            } catch (\Payrexx\PayrexxException $e) {
                $this->wh_log($e->getReason().' @Line ->'.$e->getLine());
            }

            if (!$transaction instanceof Transaction) {
                return;
            }

            // TODO Method getMetaData Missing
            // $tokenHash = $transaction->getMetaData()['paymentToken'] ?? null;
            $tokenHash = null;

        } catch (\Throwable $e) {
            $this->wh_log($e->getMessage().' @Line ->'.$e->getLine());

            throw new HttpResponse($e->getMessage(), 500, ['Content-Type' => 'text/plain', 'X-Notify-Message' => $e->getMessage()]);
        }
        $this->wh_log('start');


        if ($tokenHash === null) {
            throw new HttpResponse('OK', 200, ['Content-Type' => 'text/plain', 'X-Notify-Message' => 'NO_TOKEN_HASH_FOUND']);
        }

        try {
            $this->gateway->execute($getToken = new GetToken($tokenHash));
        } catch (\Throwable $e) {
            throw new HttpResponse('OK', 200, ['Content-Type' => 'text/plain', 'X-Notify-Message' => 'ALREADY_CLEARED_OUT']);
        }

        $this->gateway->execute(new Notify($getToken->getToken()));    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() === null;
    }
}
