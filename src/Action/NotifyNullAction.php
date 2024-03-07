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

use Payrexx\Models\Request\Transaction;
use Payum\Core\Request\Notify;
use Instride\Payum\Payrexx\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\Base;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;

class NotifyNullAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @inheritDoc
     *
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->gateway->execute($httpRequest = new GetHttpRequest());

        try {
            $parsedBody = \json_decode($httpRequest->content, true, 512, JSON_THROW_ON_ERROR);

            if ($parsedBody === false) {
                return;
            }

            $transaction = new \Payrexx\Models\Request\Transaction();
            $id = $parsedBody['transaction']['id'];
            $transaction->setId($id);

            try {
                $transaction = $this->api->getApi()->getOne($transaction);
            } catch (\Payrexx\PayrexxException $e) {
                return;
            }

            if (!$transaction instanceof Transaction) {
                return;
            }

            $tokenHash = $parsedBody['transaction']['invoice']['paymentLink']['hash'] ?? null;

        } catch (\Throwable $e) {
            throw new HttpResponse($e->getMessage(), 500, ['Content-Type' => 'text/plain', 'X-Notify-Message' => $e->getMessage()]);
        }

        if ($tokenHash === null) {
            throw new HttpResponse('OK', 200, ['Content-Type' => 'text/plain', 'X-Notify-Message' => 'NO_TOKEN_HASH_FOUND']);
        }

        try {
            $this->gateway->execute(new Notify($tokenHash));
        } catch (Base $e) {
            throw $e;
        } catch (LogicException $e) {
            throw new HttpResponse($e->getMessage(), 400, ['Content-Type' => 'text/plain']);
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() === null;
    }
}
