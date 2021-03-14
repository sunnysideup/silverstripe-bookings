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
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function FirstName($reminder, $booking, string $searchString, string $str): string
    {
        $replace = $booking->InitiatingFirstName;
        return str_replace($searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function LastName($reminder, $booking, string $searchString, string $str): string
    {
        $replace = $booking->InitiatingSurname;
        return str_replace($searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function BookingInfoHTML($reminder, $booking, string $searchString, string $str): string
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = '<table>
                            <tr>
                                <th>Date:</th>
                                <td>' . date('l, jS F Y', strtotime($booking->Tour()->Date)) . '</td>
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
        return str_replace($searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function TourTime($reminder, $booking, string $searchString, string $str): string
    {
        $replace = $booking->Tour()->TourTimeAndDate;
        return str_replace($searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function TourDuration($reminder, $booking, string $searchString, string $str): string
    {
        $replace = $booking->Tour()->Duration . ' minutes';
        return str_replace($searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function BookingConfirmationLink($reminder, $booking, string $searchString, string $str): string
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = $booking->ConfirmLink(true);
        }
        return str_replace($searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function BookingUpdateLink($reminder, $booking, string $searchString, string $str): string
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = $booking->EditLink(true);
        }
        return str_replace($searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function BookingCancellationLink($reminder, $booking, string $searchString, string $str): string
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = $booking->CancelLink(true);
        }
        return str_replace($searchString, $replace, $str);
    }

    /**
     * @param EmailReminderNotificationSchedule $reminder
     * @param DataObject $waitlister
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function SingleTourBookingForm($reminder, $waitlister, string $searchString, string $str): string
    {
        $replace = '';
        if ($waitlister instanceof Waitlister) {
            $replace = $waitlister->Tour()->JoinLink(true);
        }
        return str_replace($searchString, $replace, $str);
    }
}
