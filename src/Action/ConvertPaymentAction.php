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

namespace Instride\Payum\Payrexx\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface
{
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['order_id'] = $payment->getNumber();
        $details['currency_code'] = $payment->getCurrencyCode();
        $details['amount'] = $payment->getTotalAmount();
        $details['description'] = $payment->getDescription();

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface;
    }
}
