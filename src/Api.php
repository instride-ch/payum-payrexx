<?php

declare(strict_types=1);

/**
 * instride AG
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright 2024 instride AG (https://instride.ch)
 */

namespace Instride\Payum\Payrexx;

use Instride\Payum\Payrexx\Request\Api\CreateTransaction;
use Instride\Payum\Payrexx\Request\GetHumanStatus;
use Payrexx\Models\Base;
use Payrexx\Models\Request\Gateway as RequestGateway;
use Payrexx\Models\Request\Transaction as RequestTransaction;
use Payrexx\Models\Response\Gateway as ResponseGateway;
use Payrexx\Models\Response\Transaction as ResponseTransaction;
use Payrexx\Payrexx;
use Payrexx\PayrexxException;

class Api
{
    private string $instance;
    private string $apiKey;
    private Payrexx $api;
    private string $afterLink;

    public function __construct(Payrexx $api, string $instance, string $apiKey)
    {
        $this->api = $api;
        $this->apiKey = $apiKey;
        $this->instance = $instance;
    }

    public function createTransaction(CreateTransaction $request, string $returnUrl, string $tokenHash): Base
    {
        $model = $request->getFirstModel();
        $payrexx = new Payrexx($this->instance, $this->apiKey);

        $gateway = new RequestGateway();
        $gateway->setCurrency($model['currency_code']);
        $gateway->setVatRate(7.70);
        $gateway->setAmount($model['amount']);
        $gateway->setSuccessRedirectUrl($returnUrl);
        $gateway->addField($type = 'title', $value = $model['title']);
        $gateway->addField($type = 'forename', $value = $model['forename']);
        $gateway->addField($type = 'surname', $value = $model['surname']);
        $gateway->addField($type = 'company', $value = $model['company']);
        $gateway->addField($type = 'street', $value = $model['street']);
        $gateway->addField($type = 'postcode', $value = $model['postcode']);
        $gateway->addField($type = 'place', $value = $model['place']);
        $gateway->addField($type = 'country', $value = $model['country']);
        $gateway->addField($type = 'phone', $value = $model['phone']);
        $gateway->addField($type = 'email', $value = $model['email']);
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

    public function getTransactionByGateway($payrexxGateway): ?ResponseTransaction
    {
        if (!\in_array($payrexxGateway->getStatus(), [GetHumanStatus::STATUS_CONFIRMED, GetHumanStatus::STATUS_WAITING])) {
            return null;
        }

        $invoices = $payrexxGateway->getInvoices();

        if (!$invoices || !$invoice = \end($invoices)) {
            return null;
        }

        if (!$transactions = $invoice['transactions']) {
            return null;
        }

        return $this->getPayrexxTransaction(\end($transactions)['id']);
    }

    public function getPayrexxTransaction(int $payrexxTransactionId): ?ResponseTransaction
    {
        $payrexxTransaction = new RequestTransaction();
        $payrexxTransaction->setId($payrexxTransactionId);

        try {
            return $this->getApi()->getOne($payrexxTransaction);
        } catch(PayrexxException $e) {
            return null;
        }
    }

    public function getPayrexxGateway(int $payrexxGatewayId): ?ResponseGateway
    {
        $payrexxGateway = new RequestGateway();
        $payrexxGateway->setId($payrexxGatewayId);

        try {
            return $this->getApi()->getOne($payrexxGateway);
        } catch(PayrexxException $e) {
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
