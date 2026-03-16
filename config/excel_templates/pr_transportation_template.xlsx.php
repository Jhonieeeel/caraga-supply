<?php

return [
    'items_start_row' => 26,

    'item_columns' => [
        'start' => 'C',
        'end' => 'J',
    ],

    'markers' => [
        'delivery_period' => 'C13',
        'delivery_site' => 'C14',
        'pick_up' => 'C15',
        'reqs_vehicle' => 'C18',
        'reqs_model' => 'C19',
        'reqs_number' => 'C20',
    ],

    'fields_mapping' => [
        'transportation' => [
            'C' => 'pax_qty',
            'D' => 'itinerary',
            'E' => 'date_time',
            'F' => 'no_of_vehicles',
            'I' => 'estimated_unit_cost',
            'J' => 'calculated_cost',
        ],
    ],

    'placeholders' => [
        'project_title' => '{{project_title}}',
        'user_name' => '{{user_name}}',
        'user_designation' => '{{user_designation}}',
        'section' => '{{section}}',
        'pr_number' => '{{pr_number}}',
        'input_date' => '{{input_date}}',
        'overall_total' => '{{overall_total}}',
    ],
];
