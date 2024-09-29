<?php

namespace Sunnysideup\Bookings\Control\Middleware\ConfirmationMiddleware;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Middleware\ConfirmationMiddleware\Bypass;

/**
 * Allows a bypass when the request has been run in CLI mode
 */
class TourTasksBypass implements Bypass
{
    /**
     *
     * @param HTTPRequest $request
     *
     * @return bool
     */
    public function checkRequestForBypass(HTTPRequest $request)
    {
        $url = $request->getUrl();

        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: strpos($
          * EXP: SS5 change
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        if (strpos((string) $url, 'tourreport')) {
            return true;
        }
        return false;
    }
}
