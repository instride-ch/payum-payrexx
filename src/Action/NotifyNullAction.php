<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Exception;
use Payrexx\Payrexx;
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
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;

class NotifyNullAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = Payrexx::class;
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

            $transaction = $this->api->getApi()->getOne($parsedBody['entityId']);

            if (!$transaction) {
                return;
            }

            $token = $transaction->getMetaData()['token'] ?? null;
        } catch (Exception $e) {
            throw new HttpResponse($e->getMessage(), 500, ['Content-Type' => 'text/plain']);
        }

        if (null === $token) {
            throw new HttpResponse('Invalid Request', 400, ['Content-Type' => 'text/plain']);
        }

        try {
            $this->gateway->execute($getToken = new GetToken($token));
            $this->gateway->execute(new Notify($getToken->getToken()));
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
        return $request instanceof Notify
            && $request->getModel() === null;
    }
}
