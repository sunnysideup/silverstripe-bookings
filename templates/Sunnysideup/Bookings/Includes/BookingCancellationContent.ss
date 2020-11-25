<div class="cancellation-section">
<% if $IsCancelled %>
    <% with $CurrentBooking %>
        <p class="message good">Booking $Code has been cancelled.</p>
        <p>Would you like to <a href="$AddLink">make another booking?</a></p>
    <% end_with %>
<% else %>
    <h1>Cancel your current booking</h1>
    <% with $CurrentBooking %>
        <% include Sunnysideup\Bookings\Includes\TourBookingsBookingDetails %>
    <% end_with %>
    <div class="clear-both">
        $BookingCancellationForm
    </div>
<% end_if %>
</div>
