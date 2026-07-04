<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\ValidationLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ValidationLeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_key' => ['required', 'string', 'max:100'],
                'product_name' => ['nullable', 'string', 'max:255'],
                'source_url' => ['nullable', 'string', 'max:2048'],
                'email' => ['required', 'email:rfc', 'max:255'],
                'locale' => ['nullable', 'string', 'max:10'],
                'target_category' => ['nullable', 'string', 'max:100'],
                'price_interest' => ['nullable', Rule::in(['yes', 'maybe', 'no'])],
                'notes' => ['nullable', 'string', 'max:2000'],
                'price_seen_currency' => ['nullable', 'string', 'max:10'],
                'price_seen_amount' => ['nullable', 'numeric', 'min:0', 'max:999999'],
                'utm_source' => ['nullable', 'string', 'max:255'],
                'utm_medium' => ['nullable', 'string', 'max:255'],
                'utm_campaign' => ['nullable', 'string', 'max:255'],
                'utm_content' => ['nullable', 'string', 'max:255'],
                'utm_term' => ['nullable', 'string', 'max:255'],
                'ip_hash' => ['nullable', 'string', 'max:128'],
                'user_agent' => ['nullable', 'string', 'max:1000'],
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        }

        $client = $request->attributes->get('validation_api_client');
        if ($request->header('X-Product-Key') !== $validated['product_key'] || $client?->product_key !== $validated['product_key']) {
            return $this->validationError(ValidationException::withMessages(['product_key' => ['The product key does not match the authenticated client.']]));
        }

        [$lead, $created] = DB::transaction(function () use ($validated) {
            $lead = ValidationLead::query()->where('product_key', $validated['product_key'])->where('email', $validated['email'])->lockForUpdate()->first();
            $now = now();

            if (! $lead) {
                $lead = ValidationLead::create($validated + ['status' => 'new', 'submission_count' => 1, 'last_submitted_at' => $now]);

                return [$lead, true];
            }

            $update = collect($validated)->except(['product_key', 'email'])->all();
            if (! array_key_exists('notes', $validated)) {
                unset($update['notes']);
            }
            $update['submission_count'] = $lead->submission_count + 1;
            $update['last_submitted_at'] = $now;
            if (blank($lead->status)) {
                $update['status'] = 'new';
            }
            $lead->fill($update)->save();

            return [$lead->fresh(), false];
        });

        return response()->json(['success' => true, 'data' => ['id' => $lead->id, 'product_key' => $lead->product_key, 'created' => $created]], $created ? 201 : 200);
    }

    private function validationError(ValidationException $exception): JsonResponse
    {
        return response()->json(['success' => false, 'error' => ['code' => 'validation_error', 'message' => 'The given data was invalid.', 'fields' => $exception->errors()]], 422);
    }
}
