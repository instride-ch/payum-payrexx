<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payrexx\Models\Request\Transaction;
use Payrexx\Payrexx;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use PostFinanceCheckout\Sdk\ApiException;
use PostFinanceCheckout\Sdk\Http\ConnectionException;
use PostFinanceCheckout\Sdk\Model\LineItemCreate;
use PostFinanceCheckout\Sdk\Model\LineItemType;
use PostFinanceCheckout\Sdk\Model\TransactionCreate;
use PostFinanceCheckout\Sdk\VersioningException;
use Wvision\Payum\Payrexx\Request\PrepareTransaction;

class CaptureOffSiteAction implements ActionInterface, GenericTokenFactoryAwareInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function __construct()
    {
        $this->apiClass = Payrexx::class;
    }

    /**
     * @inheritDoc
     *
     * @param Capture $request
     *
     * @throws ApiException
     * @throws ConnectionException
     * @throws VersioningException
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        // We are back from Postfinance Checkout site, so we can skip the rest.
        if (isset($model['pfc_transaction_id'])) {
            return;
        }

        $transaction = new Transaction();
        $transaction->setCurrency($model['currency_code']);
        $transaction->setAmount(round($model['amount'] / 100, 2));

        $this->gateway->execute($transaction);

        $createdTransaction = $this->api->getApi()->create($transaction);

        $model['pfc_transaction_id'] = $createdTransaction->getId();

        throw new HttpRedirect(
            $this->api->getPaymentPageApi()->paymentPageUrl($this->api->getSpaceId(), $createdTransaction->getId())
        );
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess;
    }
}
