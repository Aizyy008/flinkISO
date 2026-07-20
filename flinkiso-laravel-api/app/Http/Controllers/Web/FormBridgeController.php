<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Form Builder bridge.
 * Reads the legacy CakePHP form builder (custom_tables) and its submissions
 * (stored in each form's own table_name) via the read-only flinkiso connection,
 * making the custom form data available inside the QMS.
 */
class FormBridgeController extends Controller
{
    /** Whitelist: only real tables in the legacy DB can be queried. */
    private function legacyTables(): array
    {
        return DB::connection('flinkiso')->table('information_schema.tables')
            ->where('table_schema', DB::connection('flinkiso')->getDatabaseName())
            ->pluck('table_name')->map(fn ($t) => strtolower($t))->all();
    }

    public function index()
    {
        $tables = $this->legacyTables();
        $forms = DB::connection('flinkiso')->table('custom_tables')
            ->where('soft_delete', 0)->orderBy('name')->get(['id', 'name', 'table_name', 'description']);

        foreach ($forms as $f) {
            $f->submissions = in_array(strtolower($f->table_name), $tables, true)
                ? DB::connection('flinkiso')->table($f->table_name)->where('soft_delete', 0)->count()
                : 0;
            $f->exists = in_array(strtolower($f->table_name), $tables, true);
        }

        return view('forms.index', compact('forms'));
    }

    public function show(string $id)
    {
        $form = DB::connection('flinkiso')->table('custom_tables')->where('id', $id)->first();
        abort_unless($form, 404);

        $exists = in_array(strtolower($form->table_name), $this->legacyTables(), true);

        // Meaningful (non-system) columns to display.
        $skip = ['id', 'sr_no', 'password', 'file_key', 'version_keys', 'record_status', 'status_user_id',
            'publish', 'soft_delete', 'branchid', 'departmentid', 'company_id', 'system_table_id',
            'created_by', 'modified_by', 'approved_by', 'prepared_by', 'division_id', 'master_list_of_format_id'];

        $columns = [];
        $rows = collect();
        if ($exists) {
            $columns = collect(DB::connection('flinkiso')->getSchemaBuilder()->getColumnListing($form->table_name))
                ->reject(fn ($c) => in_array($c, $skip, true))->values()->all();
            $rows = DB::connection('flinkiso')->table($form->table_name)
                ->where('soft_delete', 0)->latest('created')->limit(50)->get();
        }

        return view('forms.show', compact('form', 'columns', 'rows', 'exists'));
    }
}
