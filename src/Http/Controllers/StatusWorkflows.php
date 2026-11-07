<?php

declare(strict_types=1);

namespace Igniter\Cart\Http\Controllers;

use Igniter\Admin\Classes\AdminController;
use Igniter\Cart\Classes\StatusWorkflowManager;
use Igniter\Cart\Models\Order;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Flame\Exception\FlashException;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\User\Facades\AdminAuth;

class StatusWorkflows extends AdminController
{
    protected null|string|array $requiredPermissions = 'Admin.Orders';

    public function accept(string $context, string $orderId): array
    {
        $this->validate(request()->all(), [
            'minutes' => 'nullable|integer|min:0',
        ]);

        try {
            resolve(StatusWorkflowManager::class)->accept(
                $this->findOrder($orderId),
                request()->integer('minutes'),
                AdminAuth::getUser()?->getKey(),
            );
        } catch (ApplicationException $ex) {
            throw new FlashException($ex->getMessage(), code: $ex->getCode(), previous: $ex);
        }

        return [
            'message' => lang('igniter.cart::default.orders.alert_order_accepted'),
        ];
    }

    public function reject(string $context, string $orderId): array
    {
        $this->validate(request()->all(), [
            'reasonCode' => 'nullable|string',
        ]);

        try {
            resolve(StatusWorkflowManager::class)->reject(
                $this->findOrder($orderId),
                request()->string('reasonCode')->toString(),
                AdminAuth::getUser()?->getKey(),
            );
        } catch (ApplicationException $ex) {
            throw new FlashException($ex->getMessage(), code: $ex->getCode(), previous: $ex);
        }

        return [
            'message' => lang('igniter.cart::default.orders.alert_order_rejected'),
        ];
    }

    protected function findOrder(string $orderId): Order
    {
        throw_unless($orderId, new FlashException(lang('igniter.cart::default.orders.alert_missing_order_id')));

        $locationIds = LocationFacade::currentOrAssigned();
        $query = Order::query();
        if (!empty($locationIds)) {
            $query->whereHasOrDoesntHaveLocation($locationIds);
        }

        throw_unless($order = $query->find($orderId), new FlashException(
            sprintf(lang('igniter.cart::default.orders.alert_order_not_found'), $orderId),
        ));

        /** @var Order $order */
        return $order;
    }
}
