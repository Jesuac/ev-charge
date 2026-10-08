<?php

namespace App\Http\Requests;

/**
 * Same rules as storing; the reading being edited is left out of the date and neighbour checks.
 */
class UpdateMeterReadingRequest extends StoreMeterReadingRequest {}
