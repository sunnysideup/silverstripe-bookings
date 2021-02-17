<div class="tour-checkin-content row">
    <% with $CurrentTour %>
        <div class="tour-info section-of-tour-info col-md-6">
            <h3>Tour Details</h3>
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
                <dt><a href="$CMSEditLink" target="_blank">Edit in CMS</a></dt>
            </dl>
            <p><a href="$Top.CalendarLink" class="btn btn--red btn--large">Return to calendar</a></p>
        </div>
        <div class="booking-info section-of-tour-info col-md-6">
            <% if $ValidBookings %>
                <header class="tour-checkin-content__header">
                    <h3>Bookings</h3>
                    <a href="#" id="print-bookings" class="tour-checkin-content__header-link">
                        <% include Sunnysideup\Bookings\Includes\PrintIcon %>
                    </a>
                </header>
                <ol id="list-of-bookings">
                    <% loop $ValidBookings %>
                        <li>
                            <div class="tour-checkin-content__input-holder">
                                <label for="check-in-$ID">Arrived</label>
                                <input type="checkbox" class="tour-checkin-content__input-field" name='check-in-$ID' data-id="$ID" <% if $HasArrived %>checked="checked"<% end_if %>"/>
                            </div>
                            <% include Sunnysideup\Bookings\Includes\TourBookingsBookingDetailsForAdmin %>
                            <a href="$CMSEditLink"  class="btn btn--red">Edit in CMS</a>
                        </li>
                    <% end_loop %>
                </ol>
            <% else %>
                <p class="message warning">There are no bookings for this tour.</p>
            <% end_if %>
        </div>
    <% end_with %>
</div>
