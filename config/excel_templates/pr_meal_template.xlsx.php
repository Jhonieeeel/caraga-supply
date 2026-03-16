<?php

return [
    'block_start' => 15,
    'block_end' => 19,
    'header_offset' => 3,

    'title_column' => 'B',
    'title_rows' => 3,
    'total_cell' => 'L{row}',

    'item_columns' => [
        'start' => 'B',
        'end' => 'L'
    ],

    'headers' => [
        'meal' => [
            'B' => 'No. of Pax',
            'C' => 'Meal / Snack',
            'D' => 'Serving Arrangement',
            'E' => 'Inclusive Date/s',
            'F' => 'Menu',
            'G' => 'Other requirements/ Remarks'
        ],
        'accommodation' => [
            'B' => 'No. of Days',
            'C' => 'Room Type',
            'D' => 'Room Arrangement',
            'E' => 'Inclusive Date/s',
            'F' => 'Remarks',
            'G' => 'Other requirements'
        ]
    ],

    'fields_mapping' => [
        'meal' => [
            'B' => 'pax_qty',
            'C' => 'mealSnack',
            'D' => 'arrangement',
            'E' => 'delivery_date',
            'F' => 'menu',
            'G' => 'other_requirement',
            'H' => 'qty',
            'I' => 'unit',
            'J' => 'estimated_unit_cost',
            'L' => 'calculated_cost'
        ],
        'accommodation' => [
            'B' => 'no_days',
            'C' => 'room_type',
            'D' => 'room_arrangement',
            'E' => 'inclusive_dates',
            'F' => 'remarks',
            'G' => 'other_requirement',
            'H' => 'qty',
            'I' => 'unit',
            'J' => 'estimated_unit_cost',
            'L' => 'calculated_cost'
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
