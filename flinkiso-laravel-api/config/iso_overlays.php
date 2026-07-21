<?php

/*
|--------------------------------------------------------------------------
| ISO overlay fields (Milestone 2.2)
|--------------------------------------------------------------------------
| Standard-specific fields that overlay onto an Incident/Non-conformity when
| it relates to a given ISO standard. Rendered on the record screen for the
| selected standard and stored in the record's `iso_overlay` JSON column.
| Adding a standard or a field here needs no migration.
*/

return [
    'ISO 14001' => [
        'label' => 'ISO 14001 — Environmental',
        'fields' => [
            'environmental_aspect' => ['label' => 'Environmental aspect', 'type' => 'text'],
            'impact' => ['label' => 'Environmental impact', 'type' => 'text'],
            'release_to' => ['label' => 'Release to', 'type' => 'select', 'options' => ['Air', 'Water', 'Land', 'None']],
            'quantity' => ['label' => 'Quantity / measure', 'type' => 'text'],
            'permit_breach' => ['label' => 'Permit / consent breach?', 'type' => 'select', 'options' => ['No', 'Yes']],
        ],
    ],
    'ISO 45001' => [
        'label' => 'ISO 45001 — Occupational H&S',
        'fields' => [
            'injury_type' => ['label' => 'Injury type', 'type' => 'text'],
            'body_part' => ['label' => 'Body part affected', 'type' => 'text'],
            'days_lost' => ['label' => 'Days lost', 'type' => 'number'],
            'work_activity' => ['label' => 'Work activity at time', 'type' => 'text'],
            'reportable' => ['label' => 'Reportable to authority?', 'type' => 'select', 'options' => ['No', 'Yes']],
        ],
    ],
    'ISO 27001' => [
        'label' => 'ISO 27001 — Information Security',
        'fields' => [
            'affected_asset' => ['label' => 'Affected information asset', 'type' => 'text'],
            'cia_impact' => ['label' => 'CIA impact', 'type' => 'select', 'options' => ['Confidentiality', 'Integrity', 'Availability', 'Multiple']],
            'data_classification' => ['label' => 'Data classification', 'type' => 'select', 'options' => ['Public', 'Internal', 'Confidential', 'Restricted']],
            'records_affected' => ['label' => 'Records affected', 'type' => 'number'],
            'breach_reportable' => ['label' => 'Breach reportable (e.g. GDPR)?', 'type' => 'select', 'options' => ['No', 'Yes']],
        ],
    ],
    'ISO 13485' => [
        'label' => 'ISO 13485 — Medical Devices',
        'fields' => [
            'device_name' => ['label' => 'Device name', 'type' => 'text'],
            'udi' => ['label' => 'UDI / model', 'type' => 'text'],
            'lot_batch' => ['label' => 'Lot / batch', 'type' => 'text'],
            'patient_harm' => ['label' => 'Patient harm?', 'type' => 'select', 'options' => ['None', 'Potential', 'Actual']],
            'vigilance_reportable' => ['label' => 'Vigilance / MDR reportable?', 'type' => 'select', 'options' => ['No', 'Yes']],
        ],
    ],
    'ISO 17025' => [
        'label' => 'ISO 17025 — Testing & Calibration Labs',
        'fields' => [
            'method_ref' => ['label' => 'Test / calibration method', 'type' => 'text'],
            'equipment_id' => ['label' => 'Equipment ID', 'type' => 'text'],
            'measurement_uncertainty' => ['label' => 'Measurement uncertainty', 'type' => 'text'],
            'environmental_conditions' => ['label' => 'Environmental conditions', 'type' => 'text'],
            'affects_results' => ['label' => 'Affects reported results?', 'type' => 'select', 'options' => ['No', 'Yes']],
        ],
    ],
];
