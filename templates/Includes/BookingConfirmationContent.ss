<h1>Confirmation</h1>
<p class="message good">Your booking has been confirmed with the following details:</p>

<% with $CurrentBooking %>
<% include Sunnysideup\Bookings\IncludesTourBookingsBookingDetails %>
<% end_with %>

<p>
    Your will receive an email containing this information shortly.
    We looking forward to meeting you.
</p>
