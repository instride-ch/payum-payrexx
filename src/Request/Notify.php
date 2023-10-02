<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Request;

use Payum\Core\Request\Generic;

class Notify extends Generic
{
    public $transaction;

    public function __construct($model, $transaction)
    {
        parent::__construct($model);
        $this->transaction = $transaction;
    }

    /**
     * @return mixed
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param mixed $transaction
     */
    public function setTransaction($transaction): void
    {
        $this->transaction = $transaction;
    }
}

