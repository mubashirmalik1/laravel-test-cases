<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */

    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // Check if the email is already in use by an affiliate
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $existingUser->affiliate) {
            throw new AffiliateCreateException("Email $email is already in use by an affiliate.");
        }

        // Check if the email is in use by a merchant
        if ($existingUser && $existingUser->merchant) {
            throw new AffiliateCreateException("Email $email is already in use by a merchant.");
        }

        // Create a new User for the Affiliate
        $user = new User();
        $user->email = $email;
        $user->name = $name;
        $user->type = 'affiliate'; // Set the user type as 'affiliate'
        $user->save();

        // Create a new Affiliate
        $affiliate = new Affiliate();
        $affiliate->commission_rate = $commissionRate;
        $affiliate->user_id = $user->id;
        $affiliate->merchant()->associate($merchant);
        $affiliate->user()->associate($user);

        // Get a discount code from the ApiService
        $discountCode = $this->apiService->createDiscountCode($merchant);
        $affiliate->discount_code = $discountCode['code'];
        $affiliate->save();

        Log::info("Affiliate created:");
Log::info([$affiliate]);

        // Send an email notification
        Mail::to($email)->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }

}
