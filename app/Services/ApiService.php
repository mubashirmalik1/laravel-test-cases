<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * You don't need to do anything here. This is just to help
 */
class ApiService
{
    /**
     * Create a new discount code for an affiliate
     *
     * @param Merchant $merchant
     *
     * @return array{id: int, code: string}
     */
    public function createDiscountCode(Merchant $merchant): array
    {
        return [
            'id' => rand(0, 100000),
            'code' => Str::uuid()
        ];
    }

    /**
     * Send a payout to an email
     *
     * @param  string $email
     * @param  float $amount
     * @return void
     * @throws RuntimeException
     */
    public function sendPayout(string $email, float $amount)
    {
        // Perform validation checks
        if (empty($email) || $amount <= 0) {
            throw new RuntimeException("Invalid payout details");
        }

        // Assuming you have a way to actually send the payout
        // This could be an API call to a payment gateway or similar
        // For now, let's just log it as a placeholder
        Log::info("Payout sent to {$email} of amount {$amount}");

        // After sending the payout, update the order's payout status in the database
        // You need to identify the order based on the email and the amount
        // Assuming you have a model Order and a relation to affiliate user via email
        // and a field for commission owed

        $order = Order::whereHas('affiliate.user', function ($query) use ($email) {
            $query->where('email', $email);
        })->where('commission_owed', $amount)->first();

        if ($order) {
            $order->payout_status = Order::STATUS_PAID;
            $order->save();
        } else {
            // Optionally handle the case where the order is not found
            Log::warning("Order not found for the payout to {$email} of amount {$amount}");
        }

    }
}
