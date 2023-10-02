<?php

/**
 * @author Miguel Gomes
 *
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

namespace Wvision\Payum\Payrexx\Request;

use Payum\Core\Request\GetHumanStatus as BaseGetHumanStatus;

class GetHumanStatus extends BaseGetHumanStatus
{
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_WAITING = 'waiting';

    public function markConfirmed(): void
    {
        $this->status = static::STATUS_CONFIRMED;
    }

    public function markWaiting(): void
    {
        $this->status = static::STATUS_WAITING;
    }

    public function isWaiting(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_WAITING);
    }
}
