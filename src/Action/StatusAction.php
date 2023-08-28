<?php

declare(strict_types=1);

namespace Wvision\Payum\Payrexx\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        $model = $request->getModel();

        if (empty($model)) {
            $request->markNew();
        }

        if ($model['status'] == 'confirmed') {
            $request->markCaptured();
        }
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
