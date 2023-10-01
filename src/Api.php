<?php
/**
 * w-vision.
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright  Copyright (c) 2019 w-vision AG (https://www.w-vision.ch)
 */

namespace Wvision\Payum\Payrexx;

use Payrexx\Models\Request\Transaction;
use Payrexx\Payrexx;
use Payum\Core\Model\ArrayObject;
use Wvision\Payum\Payrexx\Request\Api\CreateTransaction;
use Wvision\Payum\Payrexx\Request\GetHumanStatus;

class Api
{
    private string $instance;
    private string $apiKey;
    private Payrexx $api;
    private string $afterLink;

    public function __construct(Payrexx $api,
                                string  $instance,
                                string  $apiKey)
    {
        $this->api = $api;
        $this->apiKey = $apiKey;
        $this->instance = $instance;
    }

    public function createTransaction(CreateTransaction $request, string $returnUrl, string $tokenHash): \Payrexx\Models\Base
    {
        $model = $request->getFirstModel();
        $payrexx = new \Payrexx\Payrexx($this->instance, $this->apiKey);

        $gateway = new \Payrexx\Models\Request\Gateway();
        $transactionExtender = [];
        if ($model->offsetExists('transaction_extender')) {
            $transactionExtender = $model['transaction_extender'];
        }

        $gateway->setCurrency($transactionExtender['currency']);
        $gateway->setVatRate(7.70);
        $gateway->setAmount($transactionExtender['amount']);
        $gateway->setSuccessRedirectUrl($returnUrl);
        $gateway->addField($type = 'title', $value = 'mister');
        $gateway->addField($type = 'forename', $value = 'Max');
        $gateway->addField($type = 'surname', $value = 'Mustermann');
        $gateway->addField($type = 'company', $value = 'Max Musterfirma');
        $gateway->addField($type = 'street', $value = 'Musterweg 1');
        $gateway->addField($type = 'postcode', $value = '1234');
        $gateway->addField($type = 'place', $value = 'Musterort');
        $gateway->addField($type = 'country', $value = 'AT');
        $gateway->addField($type = 'phone', $value = '+43123456789');
        $gateway->addField($type = 'email', $value = 'max.muster@payrexx.com');
        $gateway->addField($type = 'custom_field_1', $value = $tokenHash, $name = array(
            1 => 'Zahlungs Token (DE)',
            2 => 'Payment Token (EN)',
            3 => 'Token (FR)',
            4 => 'Token (IT)',
        ));

        $response = $payrexx->create($gateway);
        $this->setAfterLink($response->getLink());
        return $response;
    }

    public function getTransactionByGateway($payrexxGateway): ?\Payrexx\Models\Response\Transaction
    {
        if (!in_array($payrexxGateway->getStatus(), [GetHumanStatus::STATUS_CONFIRMED, GetHumanStatus::STATUS_WAITING])) {
            return null;
        }
        $invoices = $payrexxGateway->getInvoices();

        if (!$invoices || !$invoice = end($invoices)) {
            return null;
        }

        if (!$transactions = $invoice['transactions']) {
            return null;
        }

        return $this->getPayrexxTransaction(end($transactions)['id']);
    }

    public function getPayrexxTransaction(int $payrexxTransactionId): ?\Payrexx\Models\Response\Transaction
    {
        $payrexxTransaction = new \Payrexx\Models\Request\Transaction();
        $payrexxTransaction->setId($payrexxTransactionId);

        try {
            $response = $this->getApi()->getOne($payrexxTransaction);
            return $response;
        } catch(\Payrexx\PayrexxException $e) {
            return null;
        }
    }

    public function getPayrexxGateway(int $payrexxGatewayId): ?\Payrexx\Models\Response\Gateway
    {
        $payrexxGateway = new \Payrexx\Models\Request\Gateway();
        $payrexxGateway->setId($payrexxGatewayId);

        try {
            $response = $this->getApi()->getOne($payrexxGateway);
            return $response;
        } catch(\Payrexx\PayrexxException $e) {
            return null;
        }
    }

    public function getApi(): Payrexx
    {
        return $this->api;
    }

    public function createTransactionInfo(Transaction $transaction): array
    {
        $data = [];

        $ref = new \ReflectionClass($transaction);

        $invalidNames = [
            'getters'
        ];

        foreach ($ref->getMethods() as $method) {

            $methodName = $method->getName();

            if (!$method->isPublic()) {
                continue;
            }

            if (in_array($methodName, $invalidNames, true)) {
                continue;
            }

            if (!str_starts_with($methodName, 'get')) {
                continue;
            }

            $value = $transaction->$methodName();

            if ($value === null) {
                continue;
            }

            if (is_object($value)) {
                continue;
            }

            $data[str_replace('get', '', $methodName)] = $value;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getInstance(): string
    {
        return $this->instance;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getAfterLink(): string
    {
        return $this->afterLink;
    }

    /**
     * @param string $afterLink
     */
    public function setAfterLink(string $afterLink): void
    {
        $this->afterLink = $afterLink;
    }
}
