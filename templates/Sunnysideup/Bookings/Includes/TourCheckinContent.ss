<div class="tour-checkin-content row">
    <% with $CurrentTour %>
        <div class="booking-info section-of-tour-info col-9">
            <p><a href="$Top.CalendarLink" class="btn btn--red btn--large d-print-none">Return to calendar</a></p>
        </div>
        <div class="booking-info section-of-tour-info col-3">
            <% if $ValidBookings %>
                <header class="tour-checkin-content__header d-print-none">
                    <a href="#" id="print-bookings" class="tour-checkin-content__header-link">
                        <% include Sunnysideup\Bookings\Includes\PrintIcon %>
                    </a>
                </header>
            <% end_if %>
        </div>
        <div class="tour-info section-of-tour-info col-12">
            <h3 class="mb-5">Tour Details</h3>
            <dl id="tour-info" class="clearfix">
                <dd>Date:</dd>
                <dt>$Date.Format('EEEE'), $Date.DayOfMonth(true) $Date.Format('MMM y')</dt>
                <dd>Time:</dd>
                <dt>$StartTime.Short - $EndTime.Short</dt>
                <dd>Takes booking up to:</dd>
                <dt><% if IsFull %>Full<% else %>$TotalSpacesAtStart<% end_if %></dt>
                <dd>Booked:</dd>
                <dt>$NumberOfPlacesBooked</dt>
                <dd>Available:</dd>
                <dt>$NumberOfPlacesAvailable</dt>
                <dd>Groups:</dd>
                <dt>$NumberOfGroups</dt>
                <dd>Adults:</dd>
                <dt>$NumberOfAdults</dt>
                <dd>Children:</dd>
                <dt>$NumberOfChildren</dt>
                <dd>Edit:</dd>
                <dt class="d-print-none"><a href="$CMSEditLink" target="_blank">Edit in CMS</a></dt>
            </dl>
        </div>
        <div class="booking-info section-of-tour-info col-12">
            <% if $ValidBookings %>
                <header class="tour-checkin-content__header">
                    <h3 class="mt-5">Bookings</h3>
                </header>
                <ol id="list-of-bookings">
                    <% loop $ValidBookings %>
                        <li>
                            <h3><a href="$EditLink(true)">$InitiatingFirstName $InitiatingSurname</a></h3>
                            <% include Sunnysideup\Bookings\Includes\TourBookingsBookingDetailsForAdmin %>
                            <a href="$CMSEditLink" class="btn btn--red d-print-none">Edit in CMS</a>
                        </li>
                    <% end_loop %>
                </ol>
            <% else %>
                <p class="message warning">There are no bookings for this tour.</p>
            <% end_if %>
        </div>
    <% end_with %>
</div>
