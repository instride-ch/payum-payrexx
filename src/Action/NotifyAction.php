<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\Base;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;

class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    private function wh_log($log_msg)
    {
        $log_filename = $_SERVER['DOCUMENT_ROOT'] . "/log_test";
        if (!file_exists($log_filename)) {
            // create directory/folder uploads.
            mkdir($log_filename, 0777, true);
        }
        $log_file_data = $log_filename . '/log_' . date('d-M-Y:h:i:s') . '.log';
        file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
    }


    /**
     * @inheritDoc
     *
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        try {
            $this->gateway->execute(new Sync($request->getModel()));
       } catch (Base $e) {
           $this->wh_log('End Notify2 Null '.$e->getMessage().' @Line - '.$e->getLine());
            throw $e;
      } catch (LogicException $e) {
          $this->wh_log('End Notify2 Null '.$e->getMessage().' @Line - '.$e->getLine());
            throw new HttpResponse($e->getMessage(), 400, ['Content-Type' => 'text/plain']);
       }

       throw new HttpResponse('OK', 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        $this->wh_log('notify action2 - ' .get_class($request));

        return $request instanceof Notify;
    }
}
