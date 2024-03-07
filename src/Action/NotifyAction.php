<?php

/**
 * @author Miguel Gomes
 *
 * instride AG
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright 2024 instride AG (https://instride.ch)
 */

declare(strict_types=1);

namespace Instride\Payum\Payrexx\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Notify;
use Instride\Payum\Payrexx\Request\GetHumanStatus;

class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @inheritDoc
     *
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute(new GetHumanStatus($request->getModel()));
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof Notify;
    }
}
