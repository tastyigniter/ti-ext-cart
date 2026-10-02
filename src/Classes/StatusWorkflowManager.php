<?php

declare(strict_types=1);

namespace Igniter\Cart\Classes;

use Igniter\Admin\Models\Status;
use Igniter\Admin\Models\StatusHistory;
use Igniter\Cart\Models\Order;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Flame\Traits\EventEmitter;
use Illuminate\Support\Carbon;

class StatusWorkflowManager
{
    use EventEmitter;

    public function isEnabledFor(?int $userId): bool
    {
        if (!filter_var(setting('enable_status_workflow', true), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        $limit = collect(setting('limit_users') ?: [])
            ->map(fn(mixed $id): int => (int)$id)
            ->filter(fn(int $id): bool => $id > 0);

        if ($limit->isEmpty()) {
            return true;
        }

        return $userId !== null && $userId > 0 && $limit->contains($userId);
    }

    public function accept(Order $order, int $minutes = 0, ?int $staffId = null): Order
    {
        $acceptedStatusId = setting('accepted_order_status');
        throw_unless($acceptedStatusId, new ApplicationException(
            lang('igniter.cart::default.orders.alert_accepted_status_missing'),
        ));

        $acceptedStatus = Status::query()->find($acceptedStatusId);
        throw_unless($acceptedStatus instanceof Status, new ApplicationException(
            lang('igniter.cart::default.orders.alert_accepted_status_not_found'),
        ));

        throw_if(StatusHistory::alreadyExists($order, $acceptedStatus->getKey()), new ApplicationException(
            lang('igniter.cart::default.orders.alert_accepted_status_already_exists'),
        ));

        $statusComment = $minutes > 0 ? $this->delayComment($minutes) : null;
        if ($minutes > 0) {
            $orderDateTime = Carbon::parse($order->order_date_time)->addMinutes($minutes);
            $order->updateQuietly([
                'order_date' => $orderDateTime->toDateString(),
                'order_time' => $orderDateTime->toTimeString(),
            ]);
        }

        $order->bindEvent('model.mailGetData', function(array &$data) use ($minutes, $statusComment): void {
            $data['order_approver'] = [
                'action' => $minutes > 0 ? 'delay' : 'approve',
                'text' => $statusComment,
            ];
        });

        $order->addStatusHistory($acceptedStatus, array_filter([
            'comment' => $statusComment,
            'staff_id' => $staffId,
        ]));

        $this->fireSystemEvent('igniter.cart.orderAccepted', [$order]);

        return $order->refresh();
    }

    public function reject(Order $order, string $reasonCode, ?int $staffId = null): Order
    {
        $reasonCode = trim($reasonCode);
        throw_unless($reasonCode !== '', new ApplicationException(
            lang('igniter.cart::default.orders.alert_missing_reject_code'),
        ));

        $reason = collect(setting('rejected_reasons') ?: [])->firstWhere('code', $reasonCode);
        $rejectedStatusId = is_array($reason) ? ($reason['status_id'] ?? null) : null;
        throw_unless($rejectedStatusId, new ApplicationException(
            lang('igniter.cart::default.orders.alert_missing_reject_reason_not_found'),
        ));

        $rejectedStatus = Status::query()->find($rejectedStatusId);
        throw_unless($rejectedStatus instanceof Status, new ApplicationException(
            lang('igniter.cart::default.orders.alert_missing_reject_status_not_found'),
        ));

        throw_if(StatusHistory::alreadyExists($order, $rejectedStatus->getKey()), new ApplicationException(
            lang('igniter.cart::default.orders.alert_rejected_status_already_exists'),
        ));

        $statusComment = is_array($reason) ? (string)($reason['comment'] ?? '') : '';
        $order->bindEvent('model.mailGetData', function(array &$data) use ($statusComment): void {
            $data['order_approver'] = [
                'action' => 'decline',
                'text' => $statusComment,
            ];
        });

        $order->addStatusHistory($rejectedStatus, array_filter([
            'comment' => $statusComment,
            'staff_id' => $staffId,
        ]));

        $this->fireSystemEvent('igniter.cart.orderRejected', [$order]);

        return $order->refresh();
    }

    protected function delayComment(int $minutes): ?string
    {
        $comment = collect(setting('delay_times') ?: [])
            ->first(fn(mixed $delay): bool => is_array($delay) && (int)($delay['time'] ?? 0) === $minutes);

        if (!is_array($comment)) {
            return null;
        }

        $text = trim((string)($comment['comment'] ?? ''));

        return $text !== '' ? $text : $minutes.' minutes';
    }
}
