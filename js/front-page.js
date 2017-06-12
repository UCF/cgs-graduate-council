var $members = document.getElementById('members');

var members = [];

window.onload = function init() {
    $.ajax( {
        url: wpApiSettings.root + 'graduate/v2/members/', // wpApiSettings is defined in functions.php\twentysixteen_scripts()
        method: 'GET',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
        }
    } ).done( function ( response ) {
        members = response;
        normalizeMembers( members );

        var filteredMembers = updateMembers( members, 'council_serving_years', settings.currentYear );

        document.getElementById('members').innerHTML = renderMembersGroup("All Current Members", filteredMembers, 'council_serving_years', settings.currentYear );
        fixMemberBoxes();
    } );
};