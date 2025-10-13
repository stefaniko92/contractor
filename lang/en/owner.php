<?php

return [
    'title' => 'Owner Information',
    'page_title' => 'Owner Information',
    'navigation_label' => 'Owner Info',

    'fields' => [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'parent_name' => 'Parent Name',
        'nationality' => 'Nationality',
        'personal_id_number' => 'Personal ID Number',
        'education_level' => 'Education Level',
        'gender' => 'Gender',
        'city' => 'City',
        'municipality' => 'Municipality',
        'address' => 'Address',
        'address_number' => 'Address Number',
        'email' => 'Email',
    ],

    'gender_options' => [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
    ],

    'actions' => [
        'save' => 'Save',
    ],

    'notifications' => [
        'saved' => 'Owner information saved successfully!',
        'company_required' => 'Please fill company info first.',
    ],
];
