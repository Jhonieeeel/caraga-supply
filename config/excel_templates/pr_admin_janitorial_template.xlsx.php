<?php

return [


    'block_start' => 13,
    'block_end'   => 20,


    'item_columns' => [
        'start' => 'C',
        'end'   => 'L',
    ],


    'document_markers' => [
        'delivery_period' => ['C8', 'C10'],
        'delivery_site'   => ['C9', 'C11'],
    ],


    'block_markers' => [
        'block_title'      => 'C13',
        'total_cost'       => 'L13',
        'admin_total_cost' => 'L15',
        'jani_total_cost'  => 'L18',
    ],


    'item_groups' => [
        'administrative_items' => [
            'start_row'        => 16,
            'total_marker'     => 'admin_total_cost',
        ],
        'janitorial_items' => [
            'start_row'        => 19,
            'total_marker'     => 'jani_total_cost',
        ],
    ],

    'fields_mapping' => [
        'administrative_items' => [
            'C' => 'item_name',
            'H' => 'quantity',
            'I' => 'unit',
            'J' => 'estimated_unit_cost',
            'L' => 'estimated_cost',
        ],
        'janitorial_items' => [
            'C' => 'item_name',
            'H' => 'quantity',
            'I' => 'unit',
            'J' => 'estimated_unit_cost',
            'L' => 'estimated_cost',
        ],
    ],

    'placeholders' => [
        'project_title'    => '{{project_title}}',
        'user_name'        => '{{user_name}}',
        'user_designation' => '{{user_designation}}',
        'section'          => '{{section}}',
        'pr_number'        => '{{pr_number}}',
        'input_date'       => '{{input_date}}',
        'delivery_period'  => '{{delivery_period}}',
        'delivery_site'    => '{{delivery_site}}',
        'overall_total'    => '{{overall_total}}',
    ],
];
