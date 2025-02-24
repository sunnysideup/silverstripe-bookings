<% with $CurrentWaitlister %>
    <% if $FutureWaitlistings %>
        <h1><% if $waitlistSuccessMsg %>{$waitlistSuccessMsg}<% else %>Thanks $InitiatingFirstName! you're waitlisted for the following tours:<% end_if %></h1>
        <% loop $FutureWaitlistings %>
            <dl id="booking-info" class="clearfix">
                <% with $Tour %>
                    <dd>Date:</dd>
                        <dt>$Date.Format('EEEE'), $Date.DayOfMonth(true) $Date.Format('MMM y')</dt>
                    <dd>Time:</dd>
                        <dt>$StartTimeObj.Short - $EndTimeObj.Short</dt>
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
