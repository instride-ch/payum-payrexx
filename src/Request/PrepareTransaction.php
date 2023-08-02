<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Request;

use Payrexx\Models\Request\Transaction;
use Payum\Core\Request\Generic;

class PrepareTransaction extends Generic
{
    protected Transaction $transaction;

    public function __construct(mixed $model, Transaction $transaction)
    {
        $this->transaction = $transaction;
        parent::__construct($model);
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
