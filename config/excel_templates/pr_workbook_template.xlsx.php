<?php

return [
    'block_start' => 12,
    'block_end' => 15,
    'header_offset' => 3,

    'title_column' => 'C',
    'title_rows' => 1,
    'total_cell' => 'I{row}',

    'item_columns' => [
        'start' => 'C',
        'end' => 'I'
    ],

    'headers' => [
        'workbook' => [
            'C' => 'Particular',
            'D' => 'Date/Time of Delivery',
        ]
    ],

    'fields_mapping' => [
        'workbook' => [
            'C' => 'particular',
            'D' => 'delivery_date',
            'E' => 'qty',
            'F' => 'unit',
            'G' => 'estimated_unit_cost',
            'I' => 'calculated_cost'
        ]
    ],

    'placeholders' => [
        'project_title' => '{{project_title}}',
        'user_name' => '{{user_name}}',
        'user_designation' => '{{user_designation}}',
        'section' => '{{section}}',
        'pr_number' => '{{pr_number}}',
        'input_date' => '{{input_date}}',
        'overall_total' => '{{overall_total}}'
    ]
];
