<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: Complete this method
        // Retrieve the data from the request
        $data = $request->only(['order_id', 'subtotal_price', 'merchant_domain', 'discount_code']);

        // Call the processOrder method on the OrderService with the data
        $this->orderService->processOrder($data);

        // Return a successful response
        return response()->json(['message' => 'Order processed successfully'], 200);

    }
}
