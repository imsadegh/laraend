<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OTPVerificationController extends Controller
{
    /**
     * Send OTP to the user.
     */
    public function sendOTP(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|regex:/^09\d{9}$/', // Validate phone number format
        ]);

        $phoneNumber = $request->phone_number;
        $otpCode = rand(100000, 999999); // Generate a 6-digit OTP
        $templateId = 968053; // Replace with your SMS.ir template ID
        // $apiKey = env('SMSIR_API_KEY'); // Fetch API key from .env file
        $apiKey = 'uqEXc095XazSbgWK1BIi55Y82czWqsn7CJ6UMJzaorl09PdZ'; // Fetch API key from .env file

        // Debugging: Print the value and type of $apiKey
        // dd($apiKey, gettype($apiKey));

        $parameters = [
            [
                "name" => "Code",
                "value" => $otpCode,
            ],
        ];

        $payload = [
            "mobile" => $phoneNumber,
            "templateId" => $templateId,
            "parameters" => $parameters,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.sms.ir/v1/send/verify',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: text/plain',
                'x-api-key: ' . $apiKey,
            ],
        ]);

        $response = curl_exec($curl);

        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return response()->json(['error' => 'Failed to send OTP. ' . $error], 500);
        }

        $responseData = json_decode($response, true);

        if (isset($responseData['status']) && $responseData['status'] === 1) { // Check if status is 1 (success)
            // Store OTP in Cache with a 5-minute expiration
            Cache::put("otp_$phoneNumber", $otpCode, now()->addMinutes(5));

            // return response()->json(['message' => 'OTP sent successfully.'], 200);
            return response()->json(['message' => 'OTP sent successfully.', 'response' => $responseData], 200);
        }

        // return response()->json(['error' => 'Failed to send OTP. ' . ($responseData['message'] ?? 'Unknown error')], 500);
        return response()->json(['error' => 'Failed to send OTP. ' . ($responseData['message'] ?? 'Unknown error'), 'response' => $responseData], 500);
    }

    /**
     * Verify the OTP entered by the user.
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|regex:/^09\d{9}$/',
            'otp' => 'required|digits:6',
        ]);

        $phoneNumber = $request->phone_number;
        $otp = $request->otp;

        // Retrieve OTP from Cache
        $cachedOtp = Cache::get("otp_$phoneNumber");

        if ($cachedOtp && $cachedOtp == $otp) {
            // OTP is valid; clear it from the cache
            Cache::forget("otp_$phoneNumber");

            // Update the user to mark as verified
            $user = User::where('phone_number', $phoneNumber)->first();
            if ($user) {
                $user->update(['is_verified' => true]);
            }

            return response()->json(['message' => 'OTP verified successfully.'], 200);
        }

        return response()->json(['error' => 'Invalid or expired OTP.'], 400);
    }
}
