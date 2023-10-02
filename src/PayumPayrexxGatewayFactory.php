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

declare(strict_types=1);

namespace Wvision\Payum\Payrexx;

use Payrexx\Payrexx;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Wvision\Payum\Payrexx\Action\Api\CaptureOffsiteAction;
use Wvision\Payum\Payrexx\Action\Api\CreateTransactionAction;
use Wvision\Payum\Payrexx\Action\CaptureAction;
use Wvision\Payum\Payrexx\Action\ConvertPaymentAction;
use Wvision\Payum\Payrexx\Action\NotifyAction;
use Wvision\Payum\Payrexx\Action\NotifyNullAction;
use Wvision\Payum\Payrexx\Action\StatusAction;

class PayumPayrexxGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {

        $config->defaults([
            'payum.factory_name'        => 'payrexx',
            'payum.factory_title'       => 'Payrexx',
            'payum.action.capture'         => new CaptureAction(),
            'payum.action.status'          => new StatusAction(),
            'payum.action.notify_null'     => new NotifyNullAction(),
            'payum.action.notify'          => new NotifyAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.initialize_transaction' => new CreateTransactionAction(),
            'payum.action.api.capture_offsite'        => new CaptureOffsiteAction(),
        ]);

        $config['payum.default_options'] = [
            'instance' => '',
            'api_key' => '',
        ];

        $config->defaults($config['payum.default_options']);
        $config['payum.required_options'] = ['instance', 'api_key'];

        $config['payum.api'] = function (ArrayObject $config) {
            $config->validateNotEmpty($config['payum.required_options']);

            return new Api(new Payrexx($config['instance'], $config['api_key']), $config['instance'], $config['api_key']);
        };
    }
}
