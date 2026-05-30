<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|min:2|max:100',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'company' => 'nullable|string|max:100',
            'subject' => 'required|string|in:general,sales,support,feature,partnership,other',
            'message' => 'required|string|min:10|max:2000',
        ]);

        $contact = Contact::create($validated);

        try {
            Mail::to(config('mail.from.address'))->send(new ContactMail($contact));
        } catch (\Exception $e) {
            Log::error('Contact form mail failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Your message has been sent successfully!',
        ], 201);
    }
}
