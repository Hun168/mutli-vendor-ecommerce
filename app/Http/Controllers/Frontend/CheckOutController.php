<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use App\Models\ShippingRule;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;
use App\Models\PaymentTransaction;
use App\Services\BakongKhqrService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class CheckOutController extends Controller
{
    /**
     * Display checkout page
     */
    public function index()
    {
        $addresses = UserAddress::where('user_id', Auth::user()->id)->get();
        $shippingMethods = ShippingRule::where('status', 1)->get();
        return view('frontend.pages.checkout', compact('addresses', 'shippingMethods'));
    }

    /**
     * Store new shipping address
     */
    public function createAddress(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:200'],
            'phone' => ['required', 'max:200'],
            'email' => ['required', 'email'],
            'country' => ['required', 'max:200'],
            'state' => ['required', 'max:200'],
            'city' => ['required', 'max:200'],
            'zip' => ['required', 'max:200'],
            'address' => ['required', 'max:200']
        ]);

        $address = new UserAddress();
        $address->user_id = Auth::user()->id;
        $address->name = $request->name;
        $address->phone = $request->phone;
        $address->email = $request->email;
        $address->country = $request->country;
        $address->state = $request->state;
        $address->city = $request->city;
        $address->zip = $request->zip;
        $address->address = $request->address;
        $address->save();

        toastr('Address created successfully!', 'success', 'Success');
        return redirect()->back();
    }

    /**
     * Handle checkout form submission
     */
    public function checkOutFormSubmit(Request $request)
    {
        $request->validate([
            'shipping_method_id' => ['required', 'integer'],
            'shipping_address_id' => ['required', 'integer'],
            'payment_method' => ['nullable', 'string'],
        ]);

        $shippingMethod = ShippingRule::findOrFail($request->shipping_method_id);
        if ($shippingMethod) {
            Session::put('shipping_method', [
                'id' => $shippingMethod->id,
                'name' => $shippingMethod->name,
                'type' => $shippingMethod->type,
                'cost' => $shippingMethod->cost
            ]);
        }

        $address = UserAddress::findOrFail($request->shipping_address_id)->toArray();
        if ($address) {
            Session::put('address', $address);
        }

        Session::put('payment_method', $request->payment_method ?? 'cod');

        return response([
            'status' => 'success',
            'redirect_url' => route('user.payment')
        ]);
    }

    public function generateKhqr(Request $request)
    {
        try {
            $inputQrString = $request->input('qr_string');

            if ($inputQrString) {
                if (is_array($inputQrString) && isset($inputQrString['data']['qr'])) {
                    // Extract only data.qr and data.md5
                    $qrString = $inputQrString['data']['qr'];
                    $md5 = $inputQrString['data']['md5'] ?? md5($qrString);
                } elseif (is_string($inputQrString)) {
                    $qrString = $inputQrString;
                    $md5 = md5($qrString);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid qr_string format'
                    ], 400);
                }
            } else {
                // --- GENERATE MODE ---
                $amount = (float) $request->input('amount', 0);
                $currency = KHQRData::CURRENCY_USD;

                $individualInfo = new IndividualInfo(
                    bakongAccountID: 'meas_sotheareach@aclb',
                    merchantName: 'SOTHEAREACH MEAS',
                    merchantCity: 'PHNOM PENH',
                    currency: $currency,
                    amount: $amount
                );

                $qrString = BakongKHQR::generateIndividual($individualInfo);
                $md5 = md5($qrString);
            }

            // ✅ Return only essential data
            return response()->json([
                'qr_string' => $qrString,
                // 'md5' => $md5,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    // public function checkPaymentStatus(Request $request)
    // {
    //     $request->validate([
    //         'md5' => 'required|string',
    //     ]);

    //     $md5 = $request->input('md5');

    //     $bakongKhqr = new BakongKhqrService(); // ✅ initialize service here

    //     try {
    //         $response = $bakongKhqr->checkTransactionByMD5($md5);

    //         if ($response->failed()) {
    //             return response()->json(['status' => 'unpaid']);
    //         }

    //         $data = $response->json();

    //         $status = (isset($data['status']) && strtoupper($data['status']) === 'PAID')
    //             ? 'paid'
    //             : 'unpaid';

    //         return response()->json(['status' => $status]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Failed to check payment status',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function checkPaymentStatus(Request $request)
    {
        try {
            // $url = 'https://api-bakong.nbc.gov.kh/v1/check_transaction';
            $url = 'https://api-bakong-test.nbc.gov.kh/v1/check_transaction';

            // ✅ Correct: read credentials from .env
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Bakong-API-Key' => env('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImlkIjoiY2JhZTIwMjVjZWFhNDhkYyJ9LCJpYXQiOjE3NjA2NzIwNTgsImV4cCI6MTc2ODQ0ODA1OH0.oBv-JPoDKOQRz3kCvLHqKQZ3zmC6fiCENFXwGBkecb4'),
                'Bakong-Client-ID' => env('SOTHEAREACH MEAS'),
            ];

            $body = [
                'transactionId' => $request->md5,
                'merchantId' => env('BAKONG_MERCHANT_ID'),
            ];

            $response = Http::withHeaders($headers)->post($url, $body);

            $data = $response->json();

            if (!empty($data['transactionStatus']) && strtoupper($data['transactionStatus']) === 'PAID') {
                return response()->json(['status' => 'PAID', 'data' => $data]);
            }

            return response()->json(['status' => 'UNPAID', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'UNPAID', 'message' => $e->getMessage()], 500);
        }
    }

}