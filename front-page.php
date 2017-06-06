<?php
get_header();

$setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );

?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main" role="main">

            	<div class="content-tile">
                    <h1 class="entry-title">About the Graduate Council</h1>
                    <img src="/wp-content/themes/graduate-council/images/mh.png" id="front-page-image"/>
                    <p>
                        UCF graduate education depends on the participation of its faculty on university committees for guidance and decisions. The Graduate Council is a standing committee of the Faculty Senate and is comprised of four Graduate Committees: Appeals, Curriculum, Policy, and Program Review and Awards. A number of other working groups and committees assist with graduate education at UCF and provide valuable services.
                    </p>
                    <p>
                        Refer to the Faculty Constitution, Bylaws Section VII.B for the Graduate Council’s duties and responsibilities, membership, and committees.
                    </p>
                </div><!-- .content-tile -->
                <div style="clear:both;"></div>
              	<div class="content-tile">
                    <div style="float: right;">
                        <select id="committee-select" onchange="actionChangeCouncil( this )">
                            <option value="council_serving_years">Show all</option>
                            <option value="byCollege">List by College</option>
                            <option value="appeals_serving_years">Appeals</option>
                            <option value="curriculum_serving_years">Curriculum</option>
                            <option value="policy_serving_years">Policy</option>
                            <option value="program_serving_years">Program Review and Awards</option>
                        </select>
                    </div>
                    <h2>Current Graduate Council Members <?php echo $setting_current_year; ?></h2>
					<div class="memberRank">‡ denotes a faculty senate steering committee member</div>
					<div class="memberRank">* denotes a faculty senate member</div>
                    <div>
                        <div id="members"></div>
                        <div style="clear:both;"></div>
                   </div><!-- .content-tile -->
            </main><!-- .site-main -->
            <?php get_sidebar( 'content-bottom' ); ?>
        </div>
    </div><!-- .content-area -->
<?php
wp_register_script( 'front-page', get_template_directory_uri() . '/js/front-page.js' );
wp_localize_script( 'front-page', 'settings', array(
    'currentYear' => $setting_current_year
) );
wp_enqueue_script( 'front-page' );
?>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
