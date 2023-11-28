<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        Log::info('PROCESS ORDER');
        Log::info($data);
        Log::info('ORDER ID main');
        Log::info($data['order_id']);
Log::info(Order::count());
        // Check for duplicate order
        if (Order::where('id', $data['order_id'])->exists()) {
            // Order already exists, so ignore
            return;
        }
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

        if (!$merchant) {
            return;
        }
        // Retrieve existing affiliate or create a new one
        $affiliate = new Affiliate();
        $affiliate->user_id = $merchant->user_id;
        $affiliate->merchant_id = $merchant->id;
        $affiliate->commission_rate = $merchant->default_commission_rate;
        $affiliate->discount_code = $data['discount_code'];
        $affiliate->save();


        Log::info('AFFILIATE');

        // Create a new order
        $order = new Order();
        $order->subtotal = $data['subtotal_price'];
        $order->commission_owed = $data['subtotal_price'] * $affiliate->commission_rate;
        $order->affiliate()->associate($affiliate);

        // Find the merchant by domain
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

        if ($merchant) {
            // If the merchant is found, associate the order with the merchant
            $order->merchant()->associate($merchant);
        } else {
            return;
        }

        $order->save();
        Log::info('ORDER');
        Log::info($order);
    }


    /*public function processOrder(array $data)
    {
        // Check for duplicate order
        if (Order::where('order_id', $data['order_id'])->exists()) {
            // Order already exists, so ignore
            return;
        }

        // Handle affiliate creation or retrieval
        $affiliate = Affiliate::firstOrCreate(
            ['email' => $data['customer_email']],
            ['name' => $data['customer_name']]
        );

        // Create a new order
        $order = new Order();
        $order->order_id = $data['order_id'];
        $order->subtotal_price = $data['subtotal_price'];
        $order->merchant_domain = $data['merchant_domain'];
        $order->discount_code = $data['discount_code'];
        $order->affiliate()->associate($affiliate); // assuming an order belongs to an affiliate
        $order->save();

        // Assuming you have a way to get the Merchant object associated with the order
        // This could be through the merchant_domain or some other field in your $data
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

        // Check if a merchant is found
        if (!$merchant) {
            // Handle the case where the merchant is not found
            // This could be logging an error, throwing an exception, etc.
            return;
        }
        $commissionRate = 0.05; // 5%
        // Register the affiliate and associate with the order
        // Assuming the necessary fields are included in $data
        $affiliate = $this->affiliateService->register(
            $merchant,
            $data['customer_email'],
            $data['customer_name'],
            $commissionRate // Define or calculate the commission rate as needed
        );

        // Assuming your Order model has a method to associate with an affiliate
        $order->affiliate()->associate($affiliate);
        $order->save();
    }*/
}
