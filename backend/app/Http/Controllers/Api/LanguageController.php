<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;

class LanguageController extends Controller
{
    public function index(): JsonResponse
    {
        $languages = Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'code',
                'name',
                'native_name',
                'direction',
                'is_default',
            ]);

        return response()->json([
            'data' => $languages,
        ]);
    }
}
