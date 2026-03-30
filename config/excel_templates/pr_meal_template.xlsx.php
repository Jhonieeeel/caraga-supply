<?php

return [
    'block_start' => 15,
    'block_end' => 19,

    'accommodation_block_start' => 20,
    'accommodation_block_end'   => 24,

    'header_offset' => 3,

    'title_column' => 'B',
    'title_rows' => 3,
    'total_cell' => 'M{row}',

    'item_columns' => [
        'start' => 'B',
        'end' => 'M'
    ],

    'headers' => [
        'meal' => [
            'B' => 'No. of Pax',
            'C' => 'Meal / Snack',
            'D' => 'Serving Arrangement',
            'E' => 'Inclusive Date/s',
            'F' => 'Menu',
            'G' => 'Other requirements/Remarks'
        ],
        'accommodation' => [
            'B' => 'No. of Pax',
            'C' => 'Room Requirement',
            'D' => 'No. of Rooms',
            'E' => 'Check-In Date/Time',
            'F' => 'Check-Out Date/Time',
            'G' => 'No. of nights',
            'H' => 'Other Requirements'
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
            'I' => 'qty',
            'J' => 'unit',
            'K' => 'estimated_unit_cost',
            'M' => 'calculated_cost'
        ],
        'accommodation' => [
            'B' => 'no_of_pax',
            'C' => 'room_requirement',
            'D' => 'no_of_rooms',
            'E' => 'check_in',
            'F' => 'check_out',
            'G' => 'no_of_nights',
            'H' => 'other_requirement',
            'I' => 'qty',
            'J' => 'unit',
            'K' => 'estimated_unit_cost',
            'M' => 'calculated_cost'
        ]
    ],

    'placeholders' => [
        'project_title' => '{{project_title}}',
        'user_name' => '{{user_name}}',
        'user_designation' => '{{user_designation}}',
        'section' => '{{section}}',
        'pr_number' => '{{pr_number}}',
        'input_date' => '{{input_date}}',
        'overall_total' => '{{overall_total}}',
        'chargeability' => '{{chargeability}}'
    ]
];
