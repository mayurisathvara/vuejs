<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Plan::query()
            ->withCount('subscriptions')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%")
                        ->orWhere('billing_type', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('billing_type'), fn ($query) => $query->where('billing_type', $request->billing_type))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)))
            ->orderByRaw("FIELD(billing_type, 'trial', 'monthly', 'yearly')")
            ->orderBy('price_per_sim')
            ->orderBy('display_name');

        $perPage = min((int) $request->get('per_page', 10), 100);

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedPlan($request);

        $plan = Plan::create($validated);

        return response()->json([
            'message' => 'Plan created successfully.',
            'plan' => $plan->loadCount('subscriptions'),
        ], 201);
    }

    public function show(Plan $plan): JsonResponse
    {
        return response()->json($plan->loadCount('subscriptions'));
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $validated = $this->validatedPlan($request, $plan);

        $plan->update($validated);

        return response()->json([
            'message' => 'Plan updated successfully.',
            'plan' => $plan->fresh()->loadCount('subscriptions'),
        ]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        if ($plan->subscriptions()->exists()) {
            return response()->json([
                'message' => 'This plan has subscription history. Deactivate it instead of deleting it.',
            ], 422);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted successfully.',
        ]);
    }

    private function validatedPlan(Request $request, ?Plan $plan = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9_\\-]+$/',
                Rule::unique('plans', 'name')
                    ->where(fn ($query) => $query->where('billing_type', $request->billing_type))
                    ->ignore($plan?->id),
            ],
            'display_name' => ['required', 'string', 'max:120'],
            'billing_type' => ['required', Rule::in(['trial', 'monthly', 'yearly'])],
            'price_per_sim' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:80'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($validated['billing_type'] === 'trial' && empty($validated['trial_days'])) {
            throw ValidationException::withMessages([
                'trial_days' => ['Trial days are required for trial plans.'],
            ]);
        }

        if ($validated['billing_type'] !== 'trial') {
            $validated['trial_days'] = null;
        }

        $validated['features'] = array_values(array_unique(array_filter($validated['features'] ?? [])));

        return $validated;
    }
}
