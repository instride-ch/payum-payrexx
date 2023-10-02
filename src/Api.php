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

use Payrexx\Payrexx;
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

        $gateway->setCurrency($model['currency_code']);
        $gateway->setVatRate(7.70);
        $gateway->setAmount($model['amount']);
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
        $gateway->addField($type = 'custom_field_1', $value = $model['order_id'], $name = array(
            1 => 'Bestellung ID (DE)',
            2 => 'Order ID (EN)',
            3 => 'Commande ID (FR)',
            4 => 'Ordine ID (IT)',
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
