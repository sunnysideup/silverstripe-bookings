<?php

namespace Sunnysideup\Bookings\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use Sunnysideup\Bookings\Model\DateInfo;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\TourBookingSettings;

class TourBuilder extends BuildTask
{
    protected $title = 'Tour Builder';

    protected $description = 'Creates tours for the a number of days in the future';

    private static $number_of_days_in_the_future = 70;

    public function Link()
    {
        return '/dev/tasks/' . str_replace('\\', '-', static::class);
    }

    public function run($request)
    {
        ini_set('max_execution_time', 300);
        $today = strtotime('today');
        //revers sort to get first match!!!!
        //make sure to start at one not zero ....
        $daysInTheFuture = $this->getNumberOfDaysInFuture();
        for ($i = 1; $i <= $daysInTheFuture; ++$i) {
            $dateTS = strtotime('today +' . $i . ' day');
            $mysqlDate = date('Y-m-d', $dateTS);
            DB::alteration_message('<strong>' . $mysqlDate . '</strong>');
            $myDateInfo = DateInfo::best_match_for_date($dateTS);
            if ($myDateInfo) {
                DB::alteration_message('... found rule: ' . $myDateInfo->Title);
                $existingToursForThatDay = Tour::get()->filter(['Date' => $mysqlDate]);
                if (! $existingToursForThatDay->exists()) {
                    DB::alteration_message('... no existing tours found for that day: ', 'deleted');
                }
                $existingTourTimesForThatDay = $myDateInfo->TourTimes();
                if (! $existingTourTimesForThatDay->exists()) {
                    DB::alteration_message('... ... no template tour times found for that day', 'deleted');
                } else {
                    foreach ($existingTourTimesForThatDay as $tourTime) {
                        DB::alteration_message('... ... found tour time: ' . $tourTime->Title);
                        $myTour = $existingToursForThatDay->filter(['TourTimeID' => $tourTime->ID])->last();
                        if ($myTour && $myTour->exists()) {
                            $isNew = false;
                        //do nothing
                        } else {
                            $isNew = true;
                            $myTour = Tour::create();
                        }
                        $myTour->Date = $mysqlDate;
                        $myTour->StartTime = $tourTime->StartTime;
                        $myTour->Duration = $tourTime->Duration;
                        $myTour->PublicContent = implode('<br />', [$myDateInfo->PublicContent, $tourTime->PublicContent]);
                        $myTour->PrivateContent = implode('<br />', [$myDateInfo->PrivateContent, $tourTime->PrivateContent]);
                        $myTour->TourTimeID = $tourTime->ID;
                        $myTour->DateInfoID = $myDateInfo->ID;
                        if ($isNew) {
                            $myTour->TotalSpacesAtStart = $tourTime->NumberOfSpacesAvailable;
                        }
                        $myTour->write();
                        if ($isNew) {
                            DB::alteration_message('... ... ... created tour: ' . $myTour->getTitle(), 'created');
                        } else {
                            DB::alteration_message('... ... ... found tour: ' . $myTour->getTitle());
                        }
                    }
                }
            } else {
                DB::alteration_message('... did not find any matching rules', 'deleted');
            }
        }
        echo '<hr /><hr /><hr /><hr />END <hr /><hr /><hr /><hr />';
    }

    public function getNumberOfDaysInFuture()
    {
        $settings = TourBookingSettings::inst();

        return $settings->NumberOfDaysToGenerateToursInAdvance;
    }
}
