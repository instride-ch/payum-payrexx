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

    public function executePayment($request): array
    {
        $model = $request->getFirstModel();
        $payrexx = new \Payrexx\Payrexx($this->instance, $this->apiKey);
        $gateway = new \Payrexx\Models\Request\Gateway();
        $basket = [];

        foreach ($model->getOrder()->getItems() as $orderItem) {
            $basket[] = [
                'name' => $orderItem->getProduct()->getName(),
                'description' => $orderItem->getProduct()->getDescription(),
                'quantity' => count($orderItem->getParticipants()),
                'amount' => $orderItem->getBaseItemPriceGross(),
            ];
        }
        $gateway->setCurrency($model->getCurrencyCode());
        $gateway->setBasket($basket);
        $gateway->setVatRate(7.70);
        $gateway->setAmount($model->getTotalAmount());
        $gateway->setSuccessRedirectUrl($request->getToken()->getAfterUrl());

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
        return ['status' => 'confirmed'];
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
