<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Subscription;
use Carbon\Carbon;
use Paddle\SDK\Client;
use Paddle\SDK\Entities\Shared\Action;
use Paddle\SDK\Entities\Shared\AdjustmentType as ItemAdjustmentType;
use Paddle\SDK\Entities\Shared\TransactionStatus;
use Paddle\SDK\Resources\Adjustments\Operations\Create\AdjustmentItem;
use Paddle\SDK\Resources\Adjustments\Operations\CreateAdjustment;
use Paddle\SDK\Resources\Shared\Operations\List\OrderBy;
use Paddle\SDK\Resources\Shared\Operations\List\Pager;
use Paddle\SDK\Resources\Transactions\Operations\ListTransactions;

class PaddleSubscriptionRefundService
{
    private const FULL_REFUND_WINDOW_DAYS = 14;

    public const RESULT_NONE = 'none';

    public const RESULT_PARTIAL = 'partial';

    public const RESULT_FULL = 'full';

    public function issueRefund(Client $paddle, Subscription $subscription, ?Carbon $asOf = null): string
    {
        $asOf ??= Carbon::now();

        if (! $subscription->paddle_subscription_id) {
            return self::RESULT_NONE;
        }

        $transactions = $paddle->transactions->list(new ListTransactions(
            pager: new Pager(orderBy: OrderBy::idDescending(), perPage: 1),
            statuses: [TransactionStatus::Paid(), TransactionStatus::Completed()],
            subscriptionIds: [$subscription->paddle_subscription_id]
        ));

        $transaction = null;

        foreach ($transactions as $candidate) {
            $transaction = $candidate;
            break;
        }

        if (! $transaction) {
            return self::RESULT_NONE;
        }

        if ($this->shouldIssueFullRefund($subscription, $asOf)) {
            $paddle->adjustments->create(
                CreateAdjustment::full(
                    Action::Refund(),
                    'Full refund within first 14 days of first subscription',
                    $transaction->id
                )
            );

            return self::RESULT_FULL;
        }

        if (! $transaction->billingPeriod) {
            return self::RESULT_NONE;
        }

        $periodStart = Carbon::instance($transaction->billingPeriod->startsAt);
        $periodEnd = Carbon::instance($transaction->billingPeriod->endsAt);

        $ratio = $this->resolveRefundRatio($periodStart, $periodEnd, $asOf);

        if ($ratio === null) {
            return self::RESULT_NONE;
        }

        $items = [];

        foreach ($transaction->details->lineItems as $lineItem) {
            $lineItemTotal = (float) $lineItem->totals->total;

            if ($lineItemTotal <= 0) {
                continue;
            }

            $refundAmount = round($lineItemTotal * $ratio, 2);

            if ($refundAmount <= 0) {
                continue;
            }

            $items[] = new AdjustmentItem(
                $lineItem->id,
                ItemAdjustmentType::Proration(),
                number_format($refundAmount, 2, '.', '')
            );
        }

        if ($items === []) {
            return self::RESULT_NONE;
        }

        $paddle->adjustments->create(
            CreateAdjustment::partial(
                Action::Refund(),
                $items,
                'Prorated refund for unused subscription time',
                $transaction->id
            )
        );

        return self::RESULT_PARTIAL;
    }

    private function shouldIssueFullRefund(Subscription $subscription, Carbon $asOf): bool
    {
        if (! $subscription->created_at || $subscription->created_at->diffInDays($asOf) > self::FULL_REFUND_WINDOW_DAYS) {
            return false;
        }

        return ! $subscription->user
            ->subscriptions()
            ->whereKeyNot($subscription->id)
            ->exists();
    }

    private function resolveRefundRatio(Carbon $periodStart, Carbon $periodEnd, Carbon $asOf): ?float
    {
        if ($periodEnd->lessThanOrEqualTo($asOf)) {
            return null;
        }

        $totalSeconds = $periodEnd->diffInSeconds($periodStart);

        if ($totalSeconds <= 0) {
            return null;
        }

        $remainingSeconds = $periodEnd->diffInSeconds($asOf, false);

        if ($remainingSeconds <= 0) {
            return null;
        }

        return min(1, $remainingSeconds / $totalSeconds);
    }
}
