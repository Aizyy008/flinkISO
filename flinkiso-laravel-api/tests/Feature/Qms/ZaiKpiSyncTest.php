<?php

namespace Tests\Feature\Qms;

use App\Models\Qms\Kpi;
use App\Services\Integration\ZaiKpiClient;
use Tests\TestCase;

/**
 * FlinkISO → ZaiKPI sync payload. Verifies the ISO clause is transferred
 * alongside the standard (client acceptance fix). definitionPayload() maps model
 * attributes only, so no database is required.
 */
class ZaiKpiSyncTest extends TestCase
{
    public function test_sync_payload_includes_iso_clause_alongside_standard(): void
    {
        $kpi = new Kpi([
            'reference' => 'OTD-1', 'name' => 'On-Time Delivery', 'area' => 'quality',
            'standard' => 'ISO 9001', 'clause' => '9.1.1', 'direction' => 'higher_better',
            'status' => 'active',
        ]);

        $payload = app(ZaiKpiClient::class)->definitionPayload($kpi);

        $this->assertArrayHasKey('iso_links', $payload);
        $types = array_column($payload['iso_links'], 'link_type');
        $labels = array_column($payload['iso_links'], 'label');

        $this->assertContains('standard', $types);
        $this->assertContains('clause', $types, 'The ISO clause must be transferred to ZaiKPI.');
        $this->assertContains('9.1.1', $labels);
    }
}
