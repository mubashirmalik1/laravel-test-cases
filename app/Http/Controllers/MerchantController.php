<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        // Retrieve date range from request
        $from = $request->input('from');
        $to = $request->input('to');

        // Ensure the user is a merchant
        $merchant = $request->user()->merchant;

        // Query orders within the date range for this merchant
        $orders = Order::where('merchant_id', $merchant->id)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        // Calculate total count
        $count = $orders->count();

        // Calculate total revenue (sum of subtotals)
        $revenue = $orders->sum('subtotal');

        // Calculate commission owed only for orders with an affiliate
        $commissionsOwed = $orders->whereNotNull('affiliate_id')->sum('commission_owed');

        // Prepare and return the response
        return response()->json([
            'count' => $count,
            'revenue' => $revenue,
            'commissions_owed' => $commissionsOwed,
        ]);
    }
}
