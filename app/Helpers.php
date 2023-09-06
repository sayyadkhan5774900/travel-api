<?php

namespace App;

use Carbon\Carbon;

class Helpers
{
    public static function removeTimeFromDate($dateString)
    {
        // Try to parse the date string using Carbon
        $carbonDate = Carbon::createFromFormat('Y-m-d H:i:s', $dateString);

        // Check if parsing was successful
        if ($carbonDate !== false) {
            // Format the date without the time portion
            $dateWithoutTime = $carbonDate->format('Y-m-d');
            return $dateWithoutTime;
        }

        return $dateString; // Return the original date string if parsing fails
    }
}
