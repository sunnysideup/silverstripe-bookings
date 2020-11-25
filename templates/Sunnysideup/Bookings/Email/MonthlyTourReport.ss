<style>
    table, .outer {
        width: 100%;
        max-width: 600px;
        margin-left:auto;
        margin-right:auto;
    }
    table td,  table th {
        border: 1px solid #ccc;
        width: 50%;
    }
    table th {
        text-align: left;
        background-color: #eee;
    }
    table td {
        text-align: right;
    }
</style>
<div class="outer" style="">
    <h2><a href="$Link">Attendance from $From until $Until</a></h2>
    <table class="infotable readonly InformationTable">
        <tbody>
            <tr>
                <th>Total Number of Groups</th>
                <td>$TotalGroups</td>
            </tr>
            <tr>
                <th>Total Number of Attendees</th>
                <td>$TotalBookings</td>
            </tr>
        </tbody>
    </table>

    <% if $Nationalities %>
        <h2>Nationalities</h2>
        <table class="infotable readonly InformationTable">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Number of Groups</th>
                </tr>
            </thead>
            <tbody>
                <% loop $Nationalities %>
                    <tr>
                        <td>$Country<% if $Country == '' %>Unknown<% end_if %></td>
                        <td>$Count</td>
                    </tr>
                <% end_loop %>
            </tbody>
        </table>
    <% end_if %>

    <% if $CitiesAndTowns %>
        <h2>Cities</h2>
        <table class="infotable readonly InformationTable">
            <thead>
                <tr>
                    <th>City/Town</th>
                    <th>Number of Groups</th>
                </tr>
            </thead>
            <tbody>
                <% loop $CitiesAndTowns %>
                    <tr>
                        <td>$CityTown<% if $CityTown == '' %>Unknown<% end_if %></td>
                        <td>$Count</td>
                    </tr>
                <% end_loop %>
            </tbody>
        </table>
    <% end_if %>

    <% if $ReferralData %>
        <h2>Referral Data</h2>
        <table class="infotable readonly InformationTable">
            <tbody>
                <thead>
                    <tr>
                        <th>Referral Option</th>
                        <th>Number of Groups</th>
                    </tr>
                </thead>
                <% loop $ReferralData %>
                    <tr>
                        <td>$Title</td>
                        <td>$Count</td>
                    </tr>
                <% end_loop %>
            </tbody>
        </table>
        <% loop $ReferralData %>
            <% if $OtherResponses %>
                <h3>Reponses to "Other" referral option</h3>
                <ul>
                    <% loop $OtherResponses %>
                        <% if $Reponse %>
                            <li>$Reponse</li>
                        <% end_if %>
                    <% end_loop %>
                </ul>
            <% end_if %>
        <% end_loop %>
    <% end_if %>
</div>

