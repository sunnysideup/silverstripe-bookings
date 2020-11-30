<?php

namespace Sunnysideup\Bookings\Api;




use Sunnysideup\Bookings\Model\Booking;
use Sunnysideup\Bookings\Model\Waitlister;
use SunnySideUp\EmailReminder\Api\EmailReminder_ReplacerClassBase;



class TourBookingsEmailReminder_ReplacerClass extends EmailReminder_ReplacerClassBase
{
    protected $replaceArray = array(
        '[FIRST_NAME]' => array(
            'Title' => 'First name of the person who initiated the booking',
            'Method' => 'FirstName'
        ),
        '[LAST_NAME]' => array(
            'Title' => 'Last name of the person who initiated the booking',
            'Method' => 'LastName'
        ),
        '[BOOKING_INFO]' => array(
            'Title' => 'Information about the Booking (date, number of spaces etc)',
            'Method' => 'BookingInfoHTML'
        ),
        '[TOUR_TIME]' => array(
            'Title' => 'Date and time of the tour booking',
            'Method' => 'TourTime'
        ),
        '[TOUR_DURATION]' => array(
            'Title' => 'Duration (in minutes) of the tour',
            'Method' => 'TourDuration'
        ),
        '[CONFIRM_LINK]' => array(
            'Title' => 'Booking Confirmation Link',
            'Method' => 'BookingConfirmationLink'
        ),
        '[UPDATE_LINK]' => array(
            'Title' => 'Booking Update Link',
            'Method' => 'BookingUpdateLink'
        ),
        '[CANCEL_LINK]' => array(
            'Title' => 'Booking Cancellation Link',
            'Method' => 'BookingCancellationLink'
        ),
        '[SINGLE_TOUR_BOOKING_FORM]' => array(
            'Title' => 'Link to a Booking Form for Single Tour - only used when sending emails to those on the waitlist',
            'Method' => 'SingleTourBookingForm'
        ),
        '[DAYS]' => array(
            'Title' => 'Replaces with the number of days, as set',
            'Method' => 'Days'
        ),
        '[BEFORE_OR_AFTER]' => array(
            'Title' => 'Replaces with before or after expiry date, as set',
            'Method' => 'BeforeOrAfter'
        )
    );

    /**
     *
     * @param EmailReminder_NotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function FirstName($reminder, $booking, $searchString, $str)
    {
        $replace = $booking->InitiatingFirstName;
        $str = str_replace($searchString, $replace, $str);
        return $str;
    }

    /**
     *
     * @param EmailReminder_NotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function LastName($reminder, $booking, $searchString, $str)
    {
        $replace = $booking->InitiatingSurname;
        $str = str_replace($searchString, $replace, $str);
        return $str;
    }

    /**
     *
     * @param EmailReminder_NotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function BookingInfoHTML($reminder, $booking, $searchString, $str)
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = '<table>
                            <tr>
                                <th>Date:</th>
                                <td>'.date('l, jS F Y', strtotime($booking->Tour()->Date)).'</td>
                            </tr>
                            <tr>
                                <th>Time:</th>
                                <td>'. $booking->Tour()->StartTimeObj->Nice() .' - '. $booking->Tour()->EndTime->Nice() .'</td>
                            </tr>
                            <tr>
                                <th>Booking Code:</th>
                                <td>'.$booking->BookingReference.'</td>
                            </tr>
                            <tr>
                                <th>Total number of guests:</th>
                                <td>'.$booking->TotalNumberOfGuests.'</td>
                            </tr>
                            <tr>
                                <th>Number of adults:</th>
                                <td>'.$booking->NumberOfAdults.'</td>
                            </tr>
                            <tr>
                                <th>Number of children:</th>
                                <td>'.$booking->NumberOfChildren.'</td>
                            </tr>';
            if ($booking->SpecialAssistanceRequired) {
                $replace .= '<tr>
                                <th>Special Assistance Requested:</th>
                                <td>'.$booking->SpecialAssistanceRequiredInfo.'</td>
                            </tr>';
            }
            $replace .= '</table>';
        }
        $str = str_replace($searchString, $replace, $str);
        return $str;
    }

    /**
     *
     * @param EmailReminder_NotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function TourTime($reminder, $booking, $searchString, $str)
    {
        $replace = $booking->Tour()->TourTimeAndDate;
        $str = str_replace($searchString, $replace, $str);
        return $str;
    }

    /**
     *
     * @param EmailReminder_NotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function TourDuration($reminder, $booking, $searchString, $str)
    {
        $replace = $booking->Tour()->Duration . ' minutes';
        $str = str_replace($searchString, $replace, $str);
        return $str;
    }

    /**
     *
     * @param EmailReminder_NotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function BookingConfirmationLink($reminder, $booking, $searchString, $str)
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = $booking->ConfirmLink(true);
        }
        $str = str_replace($searchString, $replace, $str);
        return $str;
    }

    /**
     *
     * @param EmailReminder_NotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function BookingUpdateLink($reminder, $booking, $searchString, $str)
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = $booking->EditLink(true);
        }
        $str = str_replace($searchString, $replace, $str);
        return $str;
    }

    /**
     *
     * @param EmailReminder_NotificationSchedule $reminder
     * @param DataObject $booking
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function BookingCancellationLink($reminder, $booking, $searchString, $str)
    {
        $replace = '';
        if ($booking instanceof Booking) {
            $replace = $booking->CancelLink(true);
        }
        $str = str_replace($searchString, $replace, $str);
        return $str;
    }

    /**
     *
     * @param EmailReminder_NotificationSchedule $reminder
     * @param DataObject $waitlister
     * @param string $searchString
     * @param string $str
     *
     * @return string
     */
    protected function SingleTourBookingForm($reminder, $waitlister, $searchString, $str)
    {
        $replace = '';
        if ($waitlister instanceof Waitlister) {
            $replace = $waitlister->Tour()->JoinLink(true);
        }
        $str = str_replace($searchString, $replace, $str);
        return $str;
    }
}

