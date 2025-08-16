<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Page Size
    |--------------------------------------------------------------------------
    |
    | This value is the default number of items per page for data tables
    | and pagination components throughout the application.
    |
    */
    'default_page_size' => 15,

    /*
    |--------------------------------------------------------------------------
    | Maximum Page Size
    |--------------------------------------------------------------------------
    |
    | This value is the maximum number of items that can be displayed
    | per page in data tables to prevent performance issues.
    |
    */
    'max_page_size' => 100,

    /*
    |--------------------------------------------------------------------------
    | Available Page Sizes
    |--------------------------------------------------------------------------
    |
    | These are the page size options available in data table components.
    |
    */
    'page_size_options' => [10, 15, 25, 50, 100],

    /*
    |--------------------------------------------------------------------------
    | Theme Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for AdminLTE theme customization.
    |
    */
    'theme' => [
        'sidebar_mini' => true,
        'layout_fixed' => true,
        'brand_logo' => '/vendor/adminlte/dist/img/AdminLTELogo.png',
        'brand_logo_alt' => 'AdminLTE Logo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Formats
    |--------------------------------------------------------------------------
    |
    | Default date formats used throughout the application.
    |
    */
    'date_formats' => [
        'default' => 'Y-m-d H:i',
        'short' => 'M d, Y',
        'long' => 'F j, Y \a\t g:i A',
    ],
];