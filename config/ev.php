<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency Symbol
    |--------------------------------------------------------------------------
    |
    | Prefixed to the amounts shown when a price per kWh has been set on the
    | Settings page. The price itself lives in the database, not here.
    |
    */

    'currency' => env('EV_CURRENCY', '$'),

];
