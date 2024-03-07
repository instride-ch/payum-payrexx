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

use Instride\Payum\Payrexx\Action\Api\CaptureOffsiteAction;
use Instride\Payum\Payrexx\Action\Api\CreateTransactionAction;
use Instride\Payum\Payrexx\Action\CaptureAction;
use Instride\Payum\Payrexx\Action\ConvertPaymentAction;
use Instride\Payum\Payrexx\Action\NotifyAction;
use Instride\Payum\Payrexx\Action\NotifyNullAction;
use Instride\Payum\Payrexx\Action\StatusAction;
use Payrexx\Payrexx;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

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

        $config['payum.api'] = static function (ArrayObject $config) {
            $config->validateNotEmpty($config['payum.required_options']);

            return new Api(
                new Payrexx($config['instance'], $config['api_key']),
                $config['instance'],
                $config['api_key']
            );
        };
    }
}
