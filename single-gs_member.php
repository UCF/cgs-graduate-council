<?php
get_header(); ?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        $title = '';
        function roleDetails( $role ) {
            $parts = explode( ' ', $role );
            $year = $parts[0];
            $parts[0] = '';

            return array(
                'year' => $year,
                'title' => trim( implode( '', $parts ) )
            );
        }
        function title( $roles, $year ) {
            $r = '';
            $roles_split = explode( ',', $roles );
            for( $i = 0; $i < count( $roles_split ); $i++ ) {
                $role = $roles_split[ $i ];
                $roleDetails = roleDetails( $role );
                if( $roleDetails['year'] == $year && strpos( strtolower( $roleDetails['title']), 'member' ) === false ) {
                    $r .= $roleDetails['title'];
                }
            }
            return $r;
        }
        function titles( $meta, $key, $group, $year ){
            $r = '';
            if( !empty( $meta[ $key ] ) ) {
                $membership = explode( ',', $meta[ $key ][0] );
                for( $i = 0; $i < count( $membership ); $i++ ) {
                    $roleDetails = roleDetails( $membership[$i] );
                    if( $roleDetails['year'] == $year) {
                        $r .= '<ul class="memberSub">';
                        $r .= '<li>' . $group;
                        if( strtolower( trim( $roleDetails['title'] ) ) != 'member' ) :
                            $r .= '( '. $roleDetails['title'] .' )';
                        endif;
                        $r .= '</li>';
                        $r .= '</ul>';
                    }
                }

            }
            return $r;
        }
        function renderMember( $details, $settings, $year ) {
            $post = get_post();
            $setting_committee = $settings['committee'][0];

            $highest_rank = title( $details[ $setting_committee ][0], $year );

            $r = '<div class="memberBox">' .
                '<div class="memberName">' .
                $details['first_name'][0] . '&nbsp;' . $details['last_name'][0];
            if( empty( $details['faculty_senate_member'][0] ) ) { $r .= '*'; }
            $r .=       '<span class="memberRank">' . $highest_rank . '<br></span>' .
                '</div>';

            $r .= '<div class="memberDetails">'.
                $details['college'][0]. '<br>' .
                '<a href="mailto:'. $details['email'][0] .'">' . $details['email'][0] .'</a><br>'.
                '</div>';


            if( !empty(  $settings['show_committees'][0] ) ) {
                if( $setting_committee != 'curriculum_serving_years' )
                    $r .= titles( $details, 'curriculum_serving_years', 'Curriculum', $year );
                if( $setting_committee != 'appeals_serving_years' )
                    $r .= titles( $details, 'appeals_serving_years', 'Appeals and Awards', $year );
                if( $setting_committee != 'policy_serving_years' )
                    $r .= titles( $details, 'policy_serving_years', 'Policy and Procedures', $year );
                if( $setting_committee != 'program_serving_years' )
                    $r .= titles( $details, 'program_serving_years', 'Program Review', $year );
            }

            if ( $url = get_edit_post_link( $post->ID ) ) {
                $r .= '<a href="' . esc_url( $url ) . '">Edit Member</a>';
            }
            $r .= '</div>';

            return array(
                'title' => strtolower( $highest_rank ),
                'card' => $r
            );
        }

        $setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );
        $page_meta_settings = array();


        while ( have_posts() ) : the_post();
            $id = get_the_ID();
            $meta = get_post_meta( $id );
            $page_meta_settings['show_committees'] = array('on');
            ?>
            <h1><?php the_title(); ?></h1>
            <h2>Graduate Council</h2>
            <div>
                <?php
                $page_meta_settings['committee'] = array( 'council_serving_years' );
                $member = renderMember( $meta, $page_meta_settings, $setting_current_year );
                echo $member['card'];
                ?>
            </div>
            <div style="clear: both;"></div>
            <?php $page_meta_settings['show_committees'] = array(''); ?>
            <h2>Curriculum</h2>
            <div>
                <?php
                $page_meta_settings['committee'] = array( 'curriculum_serving_years' );
                $member = renderMember( $meta, $page_meta_settings, $setting_current_year );
                echo $member['card'];
                ?>
            </div>
            <div style="clear: both;"></div>
            <h2>Appeals and Awards</h2>
            <div>
                <?php
                $page_meta_settings['committee'] = array( 'appeals_serving_years' );
                $member = renderMember( $meta, $page_meta_settings, $setting_current_year );
                echo $member['card'];
                ?>
            </div>
            <div style="clear: both;"></div>
            <h2>Policy and Procedures</h2>
            <div>
                <?php
                $page_meta_settings['committee'] = array( 'policy_serving_years' );
                $member = renderMember( $meta, $page_meta_settings, $setting_current_year );
                echo $member['card'];
                ?>
            </div>
            <div style="clear: both;"></div>
            <h2>Program Review</h2>
            <div>
                <?php
                $page_meta_settings['committee'] = array( 'program_serving_years' );
                $member = renderMember( $meta, $page_meta_settings, $setting_current_year );
                echo $member['card'];
                ?>
            </div>
            <div style="clear: both;"></div>
            <div class="memberRank">* denotes a faculty senate member</div>
        <?php
        endwhile;
        wp_reset_query();

        ?>
            <div>
            </div>
            <div>
                <?php edit_post_link('Edit Page');?>
            </div>
        </div>

    </main><!-- .site-main -->

    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
