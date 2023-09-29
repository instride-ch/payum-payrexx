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
        $gateway->addField($type = 'paymentToken', $value = $tokenHash);
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
        $response = $payrexx->create($gateway);
        $this->setAfterLink($response->getLink());
        return $response;
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
