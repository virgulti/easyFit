<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Progress & BMI constants
    |--------------------------------------------------------------------------
    |
    | Constants used to derive progress, BMI progress, fat/muscle weight and
    | improvement metrics from raw daily measurements. See PLAN.md.
    |
    */

    'max_weight' => 85,

    'max_bmi' => 30,

    'con_progress' => 1.5,

    'con_bmi' => 4.5,

    'offset_progress' => 70,

    'offset_bmi' => 18,

];
