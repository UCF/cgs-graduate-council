var $members = document.getElementById('members');

var members = [];

window.onload = function init() {
    $.ajax( {
        url: wpApiSettings.root + 'graduate/v2/members/' + settings.committee, // wpApiSettings is defined in functions.php\twentysixteen_scripts()
        method: 'GET',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
        }
    } ).done( function ( response ) {
        members = response;

        // Generates titles for each committee a member belongs to.
        normalizeMembers( members );

        var filteredMembers = updateMembers( members, settings.committee, settings.currentYear ); // Filters and sorts by Committee, then Alpha

        $members.innerHTML = renderMembers( filteredMembers, settings.committee, settings.currentYear, false );
        fixMemberBoxes();
    } );
};
