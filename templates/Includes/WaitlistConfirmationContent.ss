<% with $CurrentWaitlister %>
    <% if $FutureWaitlistings %>
        <h1>Thanks $InitiatingFirstName! you're waitlisted for the following tours:</h1>
        <% loop $FutureWaitlistings %>
            <dl id="booking-info" class="clearfix">
                <% with $Tour %>
                    <dd>Date:</dd>
                    <dt>$Date.Format('l, jS M Y')</dt>
                    <dd>Time:</dd>
                    <dt>$StartTime.Nice - $EndTime.Nice</dt>
                <% end_with %>
                <dd>Total number of guests:</dd>
                <dt>$TotalNumberOfGuests</dt>
            </dl>
            <hr class="clear-both">
        <% end_loop %>
    <% else %>
        <p class="message info">You are not currently waitlisted for any tours.</p>
    <% end_if %>
<% end_with %>
