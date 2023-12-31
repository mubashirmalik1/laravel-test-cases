<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method
        $user = User::create([
            'password' => $data['api_key'],
            'email' => $data['email'],
            'name' => $data['name'],
            'type' => User::TYPE_MERCHANT
        ]);
        Log::info($user);
        //register merchant
        $merchant = new Merchant();
        $merchant->domain = $data['domain'];
        $merchant->display_name = $data['name'];
        $merchant->user_id = $user->id;
        $merchant->save();
        return $merchant;

    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method
        //update merchant
        $merchant = Merchant::where('user_id', $user->id)->first();
        $merchant->update([
            'domain' => $data['domain'],
            'display_name' => $data['name']
        ]);
        $user->update([
            'password' => $data['api_key'],
            'email' => $data['email'],
            'type' => User::TYPE_MERCHANT
        ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method
        //find merchant by email
        $user = User::where('email', $email)->first();
        if ($user) {
            return Merchant::where('user_id', $user->id)->first();
        }
        return null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method
        //pay out all of an affiliate's orders
        $orders = Order::where('affiliate_id', $affiliate->id)->where('paid', Order::STATUS_UNPAID)->get();
        foreach ($orders as $order) {
            PayoutOrderJob::dispatch($order);
        }

    }
}
