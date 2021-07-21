

<div id="legend">
    <legend>
        <strong>SL</strong> = Remaining Spaces / Total Spaces,
        <strong>G</strong> = Number of Groups,
        <strong>A</strong> = Number of Adults,
        <strong>C</strong> = Number of Children
    </legend>
    <% if $TourLinksBooking %>
    <a href="$TourLinksBooking" target="_cms" class="external-link">
        Search Bookings

        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" height="16" viewBox="0 0 16 16">
            <path fill="#c91630" d="M15.504 13.616l-3.79-3.223c-0.392-0.353-0.811-0.514-1.149-0.499 0.895-1.048 1.435-2.407 1.435-3.893 0-3.314-2.686-6-6-6s-6 2.686-6 6 2.686 6 6 6c1.486 0 2.845-0.54 3.893-1.435-0.016 0.338 0.146 0.757 0.499 1.149l3.223 3.79c0.552 0.613 1.453 0.665 2.003 0.115s0.498-1.452-0.115-2.003zM6 10c-2.209 0-4-1.791-4-4s1.791-4 4-4 4 1.791 4 4-1.791 4-4 4z"></path>
        </svg>
    </a>
    <% end_if %>
</div>

<hr/>


<div id="calendar"></div>

<hr/>

<div class="edit-tours-in-the-cms">
    <h3>Edit Tours in the CMS</h3>
    <ul>
        <% loop $TourLinks %>
            <li><a href="$CMSListLink" target="_cms" class="external-link">$plural_name</a></li>
        <% end_loop %>
    </ul>
</div>
