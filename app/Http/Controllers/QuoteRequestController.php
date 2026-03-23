<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class QuoteRequestController extends Controller
{
    /**
     * Store a new quote request.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'full_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'string', 'max:20'],
                'company_name' => ['nullable', 'string', 'max:255'],
                'genset_type' => ['required', 'in:clip-on,underslung,not_sure'],
                'rental_start_date' => ['required', 'date', 'after_or_equal:today'],
                'rental_duration_days' => ['required', 'integer', 'min:1', 'max:365'],
                'delivery_location' => ['required', 'string', 'max:1000'],
                'pickup_location' => ['nullable', 'string', 'max:1000'],
                'additional_requirements' => ['nullable', 'string', 'max:2000'],
            ]);

            // Add metadata
            $validated['source'] = 'website';
            $validated['ip_address'] = $request->ip();
            $validated['user_agent'] = $request->userAgent();
            $validated['status'] = 'new';

            // Create the quote request
            $quoteRequest = QuoteRequest::create($validated);

            // Broadcast in-app notification to all admins
            AppNotification::notify(
                null,
                'quote_request',
                'New Quote Request: ' . $quoteRequest->request_number,
                $quoteRequest->full_name . ' is requesting a quote for ' . $quoteRequest->rental_duration_days . ' day(s).',
                route('admin.quote-requests.show', $quoteRequest),
                'quote'
            );

            // Send email notifications
            $this->sendNotifications($quoteRequest);

            return response()->json([
                'success' => true,
                'message' => 'Quote request submitted successfully!',
                'request_number' => $quoteRequest->request_number,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Quote request submission failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    /**
     * Send email notifications for new quote request.
     */
    protected function sendNotifications(QuoteRequest $quoteRequest)
    {
        try {
            // Admin notification email
            Mail::send('emails.quote-request-admin', ['request' => $quoteRequest], function ($message) use ($quoteRequest) {
                $message->to('info@milelepower.co.tz')
                    ->subject('New Quote Request: ' . $quoteRequest->request_number);
            });

            // Customer confirmation email
            Mail::send('emails.quote-request-confirmation', ['request' => $quoteRequest], function ($message) use ($quoteRequest) {
                $message->to($quoteRequest->email)
                    ->subject('Quote Request Received - Milele Power');
            });

        } catch (\Exception $e) {
            Log::error('Failed to send quote request emails: ' . $e->getMessage());
            // Don't fail the request if emails fail
        }
    }
}
