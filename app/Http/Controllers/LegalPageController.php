<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\Http\JsonResponse;

class LegalPageController extends Controller
{
    public function show(string $type): JsonResponse
    {
        $page = LegalPage::where('type', $type)->first();

        if (! $page) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($page);
    }
}
