<div class="one-tour-booking">
    <dl id="booking-info" class="clearfix">
        <dd>Booking contact:</dd>
        <dt>$InitiatingFirstName $InitiatingSurname</dt>
        <% with $Tour %>
            <dd>Date:</dd>
            <dt>$Date.Format('EEEE'), $Date.DayOfMonth(true) $Date.Format('MMM y')</dt>
            <dd>Time:</dd>
            <dt>$StartTime.Short - $EndTime.Short</dt>
        <% end_with %>
        <dd>Booking code:</dd>
            <dt>$Code</dt>

        <dd>Total number of guests:</dd>
            <dt>$TotalNumberOfGuests</dt>

        <dd>Number of adults:</dd>
            <dt>$NumberOfAdults</dt>

        <dd>Number of children:</dd>
            <dt>$NumberOfChildren</dt>

        <% if $SpecialAssistanceRequired %>
        <dd>Assistance Requested:</dd>
            <dt>$SpecialAssistanceRequiredInfo</dt>
        <% end_if %>
    </dl>
    <div id="buttons-holder">
        <a href="$CancelLink(true)" class="btn">Can't make it?</a>
        <a href="$EditLink(true)" class="btn">Need to change it?</a>
    </div>
</div>
