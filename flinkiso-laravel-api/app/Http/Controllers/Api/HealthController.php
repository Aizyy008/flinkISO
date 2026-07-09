<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Health + legacy-read endpoints.
 * Confirms the QMS database (default) and the legacy FlinkISO database
 * (read-only 'flinkiso' connection) are both reachable.
 */
class HealthController extends Controller
{
    /** GET /api/health */
    public function health(): JsonResponse
    {
        return response()->json([
            'service' => 'flinkiso-laravel-api',
            'status' => 'ok',
            'qms_db' => $this->probe(null),          // default connection (qmsdb)
            'legacy_db' => $this->probe('flinkiso'),  // legacy FlinkISO (flinkisodb)
        ]);
    }

    private function probe(?string $connection): array
    {
        try {
            $db = DB::connection($connection);
            $name = $db->getDatabaseName();
            $tables = $db->table('information_schema.tables')->where('table_schema', $name)->count();
            return ['connected' => true, 'name' => $name, 'tables' => $tables];
        } catch (\Throwable $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    /** GET /api/legacy/standards — reads a legacy CakePHP-owned table (read-only). */
    public function standards(): JsonResponse
    {
        $rows = DB::connection('flinkiso')->table('standards')
            ->where('soft_delete', 0)
            ->select('id', 'name')
            ->limit(50)
            ->get();

        return response()->json(['count' => $rows->count(), 'data' => $rows]);
    }
}
