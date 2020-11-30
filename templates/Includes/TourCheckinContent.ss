<div id="tour-check-in-page">
    <% with $CurrentTour %>
        <div class="tour-info section-of-tour-info">
            <h3>Tour Details</h3>
            <dl id="tour-info" class="clearfix">
                <dd>Date:</dd>

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: .Format( (case sensitive)
  * NEW: .Format( (COMPLEX)
  * EXP: Format function for dates have changed! Please consider something like: PHP NativeFormat as an extension (see http://userguide.icu-project.org/formatparse/datetime)
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
                <dt>$Date.Format('l, jS M Y')</dt>
                <dd>Time:</dd>
                <dt>$StartTime.Nice - $EndTime.Nice</dt>

                <dd>Takes booking up to:</dd>
                <dt><% if IsFull %>Full<% else %>$TotalSpacesAtStart<% end_if %></dt>
                <hr />

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
                <dt><a href="$CMSEditLink" target="_cms">Edit in CMS</a></dt>
            </dl>
            <p><a href="$Top.CalendarLink" class="external-link">Return to calendar</a></p>
        </div>
        <div class="booking-info section-of-tour-info">
            <% if $ValidBookings %>
                <header class="clearfix">
                    <h3>Bookings</h3>
                    <a href="#" id="print-bookings">
                        <% include Sunnysideup\Bookings\IncludesPrintIcon %>
                    </a>
                </header>
                <ol id="list-of-bookings">
                    <% loop $ValidBookings %>
                        <li>
                            <div class="check-in-holder">
                                <label for="check-in-$ID">Arrived</label>
                                <input type="checkbox" class="check-in" name='check-in-$ID' data-id="$ID" <% if $HasArrived %>checked="checked"<% end_if %>"/>
                            </div>
                            <% include Sunnysideup\Bookings\IncludesTourBookingsBookingDetailsForAdmin %>
                            <a href="$CMSEditLink" class="external-link">Edit in CMS</a>
                        </li>
                    <% end_loop %>
                </ol>
            <% else %>
                <p class="message warning">There are no bookings for this tour.</p>
            <% end_if %>
        </div>
    <% end_with %>
</div>
