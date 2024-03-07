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

namespace Instride\Payum\Payrexx\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Instride\Payum\Payrexx\Api;
use Instride\Payum\Payrexx\Request\Api\CaptureOffsite;

class CaptureOffsiteAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param CaptureOffsite $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        throw new HttpRedirect(
            $this->api->getAfterLink()
        );
    }

    public function supports($request): bool
    {
        if (!$request instanceof CaptureOffsite) {
            return false;
        }

        if (!$request->getModel() instanceof \ArrayAccess) {
            return false;
        }
        return array_key_exists('gateway_id', ArrayObject::ensureArrayObject($request->getModel())->toUnsafeArray());
    }
}
