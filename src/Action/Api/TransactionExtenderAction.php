<?php

namespace Wvision\Payum\Payrexx\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Wvision\Payum\Payrexx\Request\Api\TransactionExtender;
use Wvision\Payum\Payrexx\Transaction\Transaction;

class TransactionExtenderAction implements ActionInterface
{
    /**
     * @param TransactionExtender $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        $transaction = new Transaction();

        $transaction->setId($payment->getNumber());
        $transaction->setCurrency($payment->getCurrencyCode());
        $transaction->setAmount($payment->getTotalAmount());

        $request->setTransaction($transaction);
    }

    public function supports($request): bool
    {
        return $request instanceof TransactionExtender
            && $request->getFirstModel() instanceof PaymentInterface;
    }
}
