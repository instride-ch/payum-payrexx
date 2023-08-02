<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx;

use Payrexx\Payrexx;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Wvision\Payum\Payrexx\Action\CaptureOffSiteAction;
use Wvision\Payum\Payrexx\Action\ConvertPaymentAction;
use Wvision\Payum\Payrexx\Action\NotifyAction;
use Wvision\Payum\Payrexx\Action\NotifyNullAction;
use Wvision\Payum\Payrexx\Action\StatusAction;

class PayumPayrexxCheckoutGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'payrexx',
            'payum.factory_title' => 'Payrexx',
            'payum.action.capture' => new CaptureOffSiteAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.notify_null' => new NotifyNullAction(),
        ]);

        if (!$config['payum.api']) {
            $config['payum.default_options'] = [
                'user_id' => '',
                'secret' => '',
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['user_id', 'secret'];

            $config['payum.api'] = static function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Payrexx($config['user_id'], $config['secret']);
            };
        }
    }
}
