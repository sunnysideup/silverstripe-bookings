<% with $CurrentTour %>
    <div class="booking-info section-of-tour-info">
        <% if $ValidBookings %>
            <header class="clearfix">
                <h3>Bookings</h3>
            </header>
            <ol id="list-of-bookings">
                <% loop $ValidBookings %>
                    <li>
                        <% include TourBookingsBookingDetailsSimple %>
                    </li>
                <% end_loop %>
            </ol>
        <% else %>
            <p class="message warning">There are no bookings for this tour yet.</p>
        <% end_if %>
    </div>
<% end_with %>
