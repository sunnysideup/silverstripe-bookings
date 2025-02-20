<div class="one-tour-booking">
    <dl id="booking-info" class="clearfix">
        <dd><label for="check-in-$ID">Arrived</label></dd>
        <dt><input type="checkbox" class="tour-checkin-content__input-field" id="check-in-{$ID}" name="check-in-{$ID}" data-id="{$ID}" <% if $HasArrived %>checked="checked"<% end_if %>/></dt>

        <% with $Tour %>
            <dd>Date:</dd>
                <dt>$Date.Format('EEEE'), $Date.DayOfMonth(true) $Date.Format('MMM y')</dt>

            <dd>Time:</dd>
                <dt>$StartTimeObj.Short - $EndTimeObj.Short</dt>
        <% end_with %>

        <dd>Booking code:</dd>
            <dt>$Code</dt>

        <dd>Contact:</dd>
            <dt>$InitiatingFirstName</dt>

        <dd>Email:</dd>
            <dt>$InitiatingEmail</dt>

        <dd>Phone:</dd>
            <dt>$PrimaryPhone</dt>

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
</div>
