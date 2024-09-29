<?php

namespace Sunnysideup\Bookings\Api;

use SilverStripe\ORM\DataObject;
use Sunnysideup\Bookings\Model\Booking;
use Sunnysideup\Bookings\Model\Waitlister;
use SunnySideUp\EmailReminder\Api\EmailReminderReplacerClassBase;
use SunnySideUp\EmailReminder\Model\EmailReminderNotificationSchedule;

class TourBookingsEmailReminderReplacerClass extends EmailReminderReplacerClassBase
{
    protected $replaceArray = [
        '[FIRST_NAME]' => [
            'Title' => 'First name of the person who initiated the booking',
            'Method' => 'FirstName',
        ],
        '[LAST_NAME]' => [
            'Title' => 'Last name of the person who initiated the booking',
            'Method' => 'LastName',
        ],
        '[BOOKING_INFO]' => [
            'Title' => 'Information about the Booking (date, number of spaces etc)',
            'Method' => 'BookingInfoHTML',
        ],
        '[TOUR_TIME]' => [
            'Title' => 'Date and time of the tour booking',
            'Method' => 'TourTime',
        ],
        '[TOUR_DURATION]' => [
            'Title' => 'Duration (in minutes) of the tour',
            'Method' => 'TourDuration',
        ],
        '[CONFIRM_LINK]' => [
            'Title' => 'Booking Confirmation Link',
            'Method' => 'BookingConfirmationLink',
        ],
        '[UPDATE_LINK]' => [
            'Title' => 'Booking Update Link',
            'Method' => 'BookingUpdateLink',
        ],
        '[CANCEL_LINK]' => [
            'Title' => 'Booking Cancellation Link',
            'Method' => 'BookingCancellationLink',
        ],
        '[SINGLE_TOUR_BOOKING_FORM]' => [
            'Title' => 'Link to a Booking Form for Single Tour - only used when sending emails to those on the waitlist',
            'Method' => 'SingleTourBookingForm',
        ],
        '[DAYS]' => [
            'Title' => 'Replaces with the number of days, as set',
            'Method' => 'Days',
        ],
        '[BEFORE_OR_AFTER]' => [
            'Title' => 'Replaces with before or after expiry date, as set',
            'Method' => 'BeforeOrAfter',
        ],
    ];

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject                        $booking
     */
    protected function FirstName($reminder, $booking, string $searchString, string $str): string
    {
        $replace = $booking->InitiatingFirstName;


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: str_replace($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return str_replace((string) $searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject                        $booking
     */
    protected function LastName($reminder, $booking, string $searchString, string $str): string
    {
        $replace = $booking->InitiatingSurname;


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: str_replace($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return str_replace((string) $searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject                        $booking
     */
    protected function BookingInfoHTML($reminder, $booking, string $searchString, string $str): string
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = '<table>
                            <tr>
                                <th>Date:</th>

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: strtotime($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
                                <td>' . date('l, jS F Y', strtotime((string) $booking->Tour()->Date)) . '</td>
                            </tr>
                            <tr>
                                <th>Time:</th>
                                <td>' . $booking->Tour()->StartTimeObj->Nice() . ' - ' . $booking->Tour()->EndTime->Nice() . '</td>
                            </tr>
                            <tr>
                                <th>Booking Code:</th>
                                <td>' . $booking->BookingReference . '</td>
                            </tr>
                            <tr>
                                <th>Total number of guests:</th>
                                <td>' . $booking->TotalNumberOfGuests . '</td>
                            </tr>
                            <tr>
                                <th>Number of adults:</th>
                                <td>' . $booking->NumberOfAdults . '</td>
                            </tr>
                            <tr>
                                <th>Number of children:</th>
                                <td>' . $booking->NumberOfChildren . '</td>
                            </tr>';
            if ($booking->SpecialAssistanceRequired) {
                $replace .= '<tr>
                                <th>Special Assistance Requested:</th>
                                <td>' . $booking->SpecialAssistanceRequiredInfo . '</td>
                            </tr>';
            }
            $replace .= '</table>';
        }


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: str_replace($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return str_replace((string) $searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject                        $booking
     */
    protected function TourTime($reminder, $booking, string $searchString, string $str): string
    {
        $replace = $booking->Tour()->TourTimeAndDate;


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: str_replace($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return str_replace((string) $searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject                        $booking
     */
    protected function TourDuration($reminder, $booking, string $searchString, string $str): string
    {
        $replace = $booking->Tour()->Duration . ' minutes';


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: str_replace($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return str_replace((string) $searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject                        $booking
     */
    protected function BookingConfirmationLink($reminder, $booking, string $searchString, string $str): string
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = $booking->ConfirmLink(true);
        }


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: str_replace($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return str_replace((string) $searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject                        $booking
     */
    protected function BookingUpdateLink($reminder, $booking, string $searchString, string $str): string
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = $booking->EditLink(true);
        }


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: str_replace($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return str_replace((string) $searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject                        $booking
     */
    protected function BookingCancellationLink($reminder, $booking, string $searchString, string $str): string
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = $booking->CancelLink(true);
        }


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: str_replace($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return str_replace((string) $searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject                        $waitlister
     */
    protected function SingleTourBookingForm($reminder, $waitlister, string $searchString, string $str): string
    {
        $replace = '';
        if ($waitlister instanceof Waitlister) {
            $replace = $waitlister->Tour()->JoinLink();
        }


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: str_replace($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return str_replace((string) $searchString, $replace, $str);
    }
}
