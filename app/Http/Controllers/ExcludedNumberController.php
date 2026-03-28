<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExcludedNumber;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExcludedNumberController extends Controller
{
    /**
     * Display a listing of excluded numbers.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = ExcludedNumber::select(['id', 'phone_number', 'label', 'organization_id', 'created_at', 'updated_at']);

        // Scope to own organization for non-admin roles
        if ($user->role !== 'admin') {
            $query->where('organization_id', $user->organization_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            if (strlen($search) >= 2) {
                $query->where(function ($q) use ($search) {
                    $q->where('phone_number', 'like', "%{$search}%")
                      ->orWhere('label', 'like', "%{$search}%");
                });
            }
        }

        // Organization filter (admin only)
        if ($user->role === 'admin' && $request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        $query->orderBy('created_at', 'desc');

        $perPage = min((int) $request->get('per_page', 10), 50);
        $numbers = $query->with(['organization:id,name'])->paginate($perPage);

        return response()->json($numbers);
    }

    /**
     * Get organizations for dropdown.
     */
    public function getOrganizations(): JsonResponse
    {
        $user = auth()->user();

        $query = Organization::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name');

        if ($user->role !== 'admin') {
            $query->where('id', $user->organization_id);
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created excluded number.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        $organizationId = ($user->role !== 'admin')
            ? $user->organization_id
            : $request->organization_id;

        $rules = [
            'phone_number' => ['required', 'digits:10'],
            'label'        => 'nullable|string|max:255',
        ];

        if ($user->role === 'admin') {
            $rules['organization_id'] = 'required|exists:organizations,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $number = ExcludedNumber::create([
            'phone_number'    => $request->phone_number,
            'label'           => $request->label,
            'organization_id' => $organizationId,
        ]);

        $number->load('organization:id,name');

        return response()->json($number, 201);
    }

    /**
     * Display the specified excluded number.
     */
    public function show(ExcludedNumber $excludedNumber): JsonResponse
    {
        $excludedNumber->load('organization:id,name');
        return response()->json($excludedNumber);
    }

    /**
     * Update the specified excluded number.
     */
    public function update(Request $request, ExcludedNumber $excludedNumber): JsonResponse
    {
        $user = auth()->user();

        $organizationId = ($user->role !== 'admin')
            ? $user->organization_id
            : $request->organization_id;

        $rules = [
            'phone_number' => ['required', 'digits:10'],
            'label'        => 'nullable|string|max:255',
        ];

        if ($user->role === 'admin') {
            $rules['organization_id'] = 'required|exists:organizations,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $excludedNumber->update([
            'phone_number'    => $request->phone_number,
            'label'           => $request->label,
            'organization_id' => $organizationId,
        ]);

        $excludedNumber->load('organization:id,name');

        return response()->json($excludedNumber);
    }

    /**
     * Remove the specified excluded number.
     */
    public function destroy(ExcludedNumber $excludedNumber): JsonResponse
    {
        $excludedNumber->delete();

        return response()->json(['message' => 'Excluded number deleted successfully']);
    }

    /**
     * Import excluded numbers from a CSV file.
     * CSV columns: phone_number, label (optional)
     * organization_id is auto-resolved from the authenticated user.
     */
    public function importCsv(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:5120', // 5 MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Resolve organization_id
        if ($user->role === 'admin') {
            $organizationId = $request->input('organization_id');
            if (!$organizationId) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => ['organization_id' => ['Organization is required for admin import.']],
                ], 422);
            }
            if (!Organization::find($organizationId)) {
                return response()->json(['message' => 'Organization not found.'], 404);
            }
        } else {
            $organizationId = $user->organization_id;
        }

        try {
            $file    = $request->file('file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            if (empty($csvData)) {
                return response()->json(['message' => 'CSV file is empty.'], 422);
            }

            // Normalize headers
            $headers = array_map('trim', array_map('strtolower', $csvData[0]));
            array_shift($csvData);

            if (!in_array('phone_number', $headers)) {
                return response()->json([
                    'message'          => 'Missing required column: phone_number',
                    'required_columns' => ['phone_number', 'label (optional)'],
                ], 422);
            }

            $imported = 0;
            $skipped  = 0;
            $errors   = [];
            $row      = 1;

            foreach ($csvData as $data) {
                $row++;

                // Skip completely empty rows
                if (empty(array_filter($data))) {
                    continue;
                }

                if (count($data) < count($headers)) {
                    $errors[] = "Row {$row}: Insufficient columns";
                    continue;
                }

                $rowData     = array_combine($headers, $data);
                $phoneNumber = trim($rowData['phone_number'] ?? '');
                $label       = trim($rowData['label'] ?? '');

                if (empty($phoneNumber)) {
                    $errors[] = "Row {$row}: phone_number is required";
                    continue;
                }

                if (!preg_match('/^\d{10}$/', $phoneNumber)) {
                    $errors[] = "Row {$row}: phone_number must be exactly 10 digits";
                    continue;
                }

                // Skip duplicates within the same organization
                if (ExcludedNumber::where('phone_number', $phoneNumber)
                        ->where('organization_id', $organizationId)
                        ->exists()) {
                    $skipped++;
                    continue;
                }

                ExcludedNumber::create([
                    'phone_number'    => $phoneNumber,
                    'label'           => $label ?: null,
                    'organization_id' => $organizationId,
                ]);

                $imported++;
            }

            return response()->json([
                'message'  => "Import complete. {$imported} imported, {$skipped} skipped (duplicates).",
                'imported' => $imported,
                'skipped'  => $skipped,
                'errors'   => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process CSV file.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
