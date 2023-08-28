<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx;

use ArrayObject;
use Payrexx\Payrexx;
use Payum\Core\GatewayFactory;
use Wvision\Payum\Payrexx\Action\CaptureAction;
use Wvision\Payum\Payrexx\Action\StatusAction;

class PayumPayrexxCheckoutGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'payrexx',
            'payum.factory_title' => 'Payrexx Checkout',
            'payum.action.status' => new StatusAction(),
            'payum.action.capture' => new CaptureAction(),
        ]);

        $config['payum.default_options'] = [
            'instance' => '',
            'api_key' => '',
        ];

        $config->defaults($config['payum.default_options']);
        $config['payum.required_options'] = ['instance', 'api_key'];

        $config['payum.api'] = function (ArrayObject $config) {
            $config->validateNotEmpty($config['payum.required_options']);

            return new Payrexx($config['instance'], $config['api_key']);
        };
    }
}
