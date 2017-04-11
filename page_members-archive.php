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


        function JSONMember( $meta ) {
            $post = get_post();

            $r = '{';
            $r .= '"first_name": "'                                  . $meta['first_name'][0] . '",';
            $r .= '"last_name": "'                                   . $meta['last_name'][0] . '",';
            $r .= '"email": "'                                       . $meta['email'][0] . '",';
            $r .= '"college": "'                                     . $meta['college'][0] . '",';
            $r .= '"department": "'                                  . $meta['department'][0] . '",';
            $r .= '"faculty_senate_member": "'                       . $meta['faculty_senate_member'][0] . '",';
            $r .= '"faculty_senate_steering_committee_member": "'    . $meta['faculty_senate_steering_committee_member'][0] . '",';


            $council_serving_years      = ( !empty($meta['council_serving_years'][0]) )? $meta['council_serving_years'][0]: '';
            $curriculum_serving_years   = ( !empty($meta['curriculum_serving_years'][0]) )? $meta['curriculum_serving_years'][0]: '';
            $policy_serving_years       = ( !empty($meta['policy_serving_years'][0]) )? $meta['policy_serving_years'][0]: '';
            $appeals_serving_years      = ( !empty($meta['appeals_serving_years'][0]) )? $meta['appeals_serving_years'][0]: '';
            $program_serving_years      = ( !empty($meta['program_serving_years'][0]) )? $meta['program_serving_years'][0]: '';

            $r .= '"council_serving_years": "'                       . $council_serving_years . '",';
            $r .= '"curriculum_serving_years": "'                    . $curriculum_serving_years . '",';
            $r .= '"policy_serving_years": "'                        . $policy_serving_years . '",';
            $r .= '"appeals_serving_years": "'                       . $appeals_serving_years . '",';
            $r .= '"program_serving_years": "'                       . $program_serving_years . '"';
            if ( $url = get_edit_post_link( $post->ID ) ) {
                $r .= ',"url": "'                                     . esc_url( $url ) . '"';
            }

            $r .= '}';

            return $r;
        }





        while ( have_posts() ) : the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $page_meta_settings = get_post_meta( $id );
            $setting_committee = $page_meta_settings['committee'][0];

            ?>
            <!--<div class="content-tile">
            <h1><?php the_title(); ?></h1>
            <p>
                <?php the_content(); ?>
            </p>
            </div>-->
            <?php
        endwhile;
        wp_reset_query();

        ?>
        <div class="content-tile">
            <h1 id="member"><?php echo $title; ?></h1>
            <div class="memberRank">* denotes a faculty senate member</div>
            <div>

                <script>
                    var settingsCommittee = "<?php echo $setting_committee; ?>";
                    var members = [];
                <?php
                $args = array(
                'post_type' => 'gs_member',
                'posts_per_page' => -1,
                    'meta_query' =>
                        array(
                            array(
                                'key'       => $setting_committee,
                                'value'     => '',
                                'compare'   => '!=',
                            )
                        ),
                );
                $years = array();
                $members = array();
                $query_members = new WP_Query( $args );
                $first = true;
                if($query_members->have_posts()):
                    echo 'var members = [';
                    while ($query_members->have_posts()):
                        $query_members->the_post();

                        $meta = get_post_meta( $post->ID );

                        if( !$first )
                            echo ',';
                        else
                            $first = false;
                        echo JSONMember( $meta );

                    endwhile;
                    echo ']';
                endif;
                wp_reset_query();

                ?>
                </script>
                <div class="col-xs-3">
                    <h4>Years</h4>
                    <div id="years">

                    </div>
                </div>
                <div class="col-xs-9">
                    <h4>Members</h4>
                    <div id="members">

                    </div>
                </div>

                <div style="clear: both;"></div>

                <div>
                    <?php edit_post_link('Edit Page');?>
                </div>
                <script>
                    var _current_year = "<?php echo $setting_current_year; ?>";
                    var _council = "<?php echo $setting_committee; ?>";

                    var $members = document.getElementById('members');
                    var $years = document.getElementById('years');


                    window.onload = function init(){
                        normalizeMembers( members );

                        var years = getQueryVariable("years");

                        if( !/\d{4,}-\d{4,}/ig.test( years ) )
                            years = _current_year;

                        var filteredMembers = updateMembers( members, _council, years );

                        $members.innerHTML = renderMembers( filteredMembers, _council, years, false );

                        $years.innerHTML = renderYears( generateYears( members, _council ), years );


                        fixMemberBoxes();
                    };
                </script>
            </div>
        </div>
    </main><!-- .site-main -->

    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
