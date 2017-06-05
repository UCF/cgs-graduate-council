<?php
/**
 * Template Name: Members Archive
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
get_header(); ?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        $title = '';
        $setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );
        $setting_committee = '';
        $page_meta_settings = array();

        while ( have_posts() ) : the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $page_meta_settings = get_post_meta( $id );
            $setting_committee = $page_meta_settings['committee'][0];
        endwhile;
        wp_reset_query();

        ?>
        <div class="content-tile">
            <h1 id="member"><?php echo $title; ?></h1>
            <div class="memberRank">‡ denotes a faculty senate steering committee member</div>
            <div class="memberRank">* denotes a faculty senate member</div>
            <div>
                <div class="col-xs-3">
                    <h3><span>Year</span></h3>
                    <div id="years">

                    </div>
                </div>
                <div class="col-xs-9">
                    <div id="members"></div>
                </div>
                <div style="clear: both;"></div>
                <div>
                    <?php edit_post_link('Edit Page');?>
                </div>
                <script>
                    var _current_year = "<?php echo $setting_current_year; ?>";
                    var _committee = "<?php echo $setting_committee; ?>";

                    var $members = document.getElementById('members');
                    var $years = document.getElementById('years');

                    var members = [];

                    window.onload = function init(){
                        var years = getQueryVariable("years");

                        if( !/\d{4,}-\d{4,}/ig.test( years ) )
                            years = _current_year;

                        $.ajax( {
                            url: wpApiSettings.root + 'graduate/v2/members/' + _committee, // wpApiSettings is defined in functions.php\twentysixteen_scripts()
                            method: 'GET',
                            beforeSend: function ( xhr ) {
                                xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
                            }
                        } ).done( function ( response ) {
                            members = response;

                            // Generates titles for each committee a member belongs to.
                            normalizeMembers( members );

                            var filteredMembers = updateMembers( members, _committee, years ); // Filters and sorts by Committee, then Alpha

                            $members.innerHTML = renderMembers( filteredMembers, _committee, years, false );
                            $years.innerHTML = renderYears( generateYears( members, _committee ), years );
                            fixMemberBoxes();
                        } );
                    };
                </script>
            </div>
        </div>
    </main><!-- .site-main -->

    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
