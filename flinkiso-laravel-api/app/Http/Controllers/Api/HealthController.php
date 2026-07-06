<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Health + legacy-read endpoints — proves the Laravel service shares the
 * FlinkISO database and can read CakePHP-owned tables.
 */
class HealthController extends Controller
{
    /** GET /api/health */
    public function health(): JsonResponse
    {
        try {
            $tables = DB::table('information_schema.tables')
                ->where('table_schema', DB::getDatabaseName())
                ->count();
            $db = ['connected' => true, 'name' => DB::getDatabaseName(), 'tables' => $tables];
        } catch (\Throwable $e) {
            $db = ['connected' => false, 'error' => $e->getMessage()];
        }

        return response()->json([
            'service' => 'flinkiso-laravel-api',
            'status' => 'ok',
            'database' => $db,
        ]);
    }

    /** GET /api/legacy/standards — reads a CakePHP-owned table (read-only). */
    public function standards(): JsonResponse
    {
        $rows = DB::table('standards')
            ->where('soft_delete', 0)
            ->select('id', 'name')
            ->limit(50)
            ->get();

        return response()->json(['count' => $rows->count(), 'data' => $rows]);
    }
}
