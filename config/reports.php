<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Report Storage Duration
    |--------------------------------------------------------------------------
    | Number of days generated report files are retained before automatic
    | deletion by the scheduled cleanup command.
    | Configure via REPORT_STORAGE_DAYS in your .env file.
    */
    'storage_days' => (int) env('REPORT_STORAGE_DAYS', 2),
];
