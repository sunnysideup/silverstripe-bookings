<div class="one-tour-booking">
    <dl id="booking-info" class="clearfix">
        <dd>Booking Contact:</dd>
        <dt>$InitiatingFirstName $InitiatingSurname</dt>
        <% with $Tour %>
            <dd>Date:</dd>
            <dt>$Date.Format('l, jS M Y')</dt>
            <dd>Time:</dd>
            <dt>$StartTime.Nice - $EndTime.Nice</dt>
        <% end_with %>
        <dd>Booking Code:</dd>
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
        <a href="$CancelLink(true)" class="button">Can't make it?</a>
        <a href="$EditLink(true)" class="button">Need to change it?</a>
    </div>
</div>
