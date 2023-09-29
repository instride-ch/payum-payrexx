<?php
declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Request\Api;

use Payum\Core\Request\Generic;
use Wvision\Payum\Payrexx\Transaction\Transaction;

class TransactionExtender extends Generic
{
    protected Transaction $transaction;

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function toArray(): array
    {
        return $this->transaction->toArray();
    }
}
