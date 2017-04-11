<?php
/**
 * Template Name: Meetings Schedule
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
get_header(); ?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        $setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );
        $setting_committee = '';
        $page_meta_settings = array();


        while ( have_posts() ) : the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $page_meta_settings = get_post_meta( $id );
            $setting_committee = $page_meta_settings['committee'][0];
            $setting_hasMinutes = $page_meta_settings['hasMinutes'][0];
            $setting_hasAgenda = $page_meta_settings['hasAgenda'][0];
            $setting_hasSubmitDate = $page_meta_settings['hasSubmitDate'][0];
            ?>
            <h1><?php the_title(); ?></h1>
            <p>
                <?php the_content(); ?>
            </p>
            <?php
        endwhile;
        wp_reset_query();

        ?>
        <div>
            <?php
            $args = array(
                'post_type' => 'gs_meetings',
                'posts_per_page' => -1,
                'meta_query' =>
                    array(
                        array(
                            'key'       => 'council',
                            'value'     => $setting_committee,
                            'compare'   => '=',
                        )
                    ),
            );
            ?>
            <div>
                <table>
                    <thead>
                        <tr>
                            <?php
                            echo '<th>Meeting Date and Time</th>';
                            if( !empty($setting_hasSubmitDate) )
                                echo '<th>Submit Date</th>';
                            if( !empty($setting_hasAgenda))
                                echo '<th>Agenda</th>';
                            if( !empty($setting_hasMinutes) )
                                echo '<th>Minutes</th>';
                            ?>
                        </tr>
                    </thead>
                    <tbody>
            <?php
            $meetings = array();
            $query_meetings = new WP_Query( $args );
            if($query_meetings->have_posts()):
                while ($query_meetings->have_posts()):
                    $query_meetings->the_post();

                    $meta = get_post_meta( $post->ID );
                ?>
                    <tr>
                        <?php
                            $date = explode( '/', $meta['date'][0] );
                            $stamp = mktime( 0, 0, 0, $date[0], $date[1], $date[2] );

                            echo '<td>' . $meta['date'][0] . ' ' . $meta['location'][0];
                            edit_post_link('Edit Meeting');
                            echo '</td>';
                            if( !empty($setting_hasSubmitDate) )
                                echo '<td>' . $meta['deadline'][0] . '</td>';
                            if( !empty($setting_hasAgenda)) {
                                echo '<td>';
                                if( !empty( $meta['agenda_id'] ) && $meta['agenda_id'][0] != 0 ) {
                                    echo '<a href="'. get_permalink( $meta['agenda_id']['0'] ) .'">Agenda</a>';
                                }
                                echo '</td>';
                            }
                            if( !empty($setting_hasMinutes) ) {
                                echo '<td>';
                                if( !empty( $meta['minutes_id'] ) && $meta['minutes_id'][0] != 0 ) {
                                    echo '<a href="'. get_permalink( $meta['minutes_id']['0'] ) .'">Minutes</a>';
                                }
                                echo '</td>';
                            }

                        ?>
                    </tr>
                <?php
                endwhile;
            endif;
            wp_reset_query();


            ?>
                    </tbody>
                </table>
            </div>
            <div style="clear: both;"></div>
            <div>
                <?php edit_post_link('Edit Page');?>
            </div>
        </div>

    </main><!-- .site-main -->

    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
