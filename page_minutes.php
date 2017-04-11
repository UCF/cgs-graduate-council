<?php
/**
 * Template Name: Minutes Directory
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
        function roleDetails( $role ) {
            $parts = explode( ' ', $role );
            $year = $parts[0];
            $parts[0] = '';

            return array(
                'year' => $year,
                'title' => trim( implode( '', $parts ) )
            );
        }

        $setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );
        $setting_committee = '';
        $page_meta_settings = array();


        while ( have_posts() ) : the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $page_meta_settings = get_post_meta( $id );
            $setting_committee = $page_meta_settings['committee'][0];
            ?>
            <h1><?php the_title(); ?></h1>
            <p>
                <?php the_content(); ?>
            </p>
            <?php
        endwhile;
        wp_reset_query();

        ?>
        <h2><?php echo $title; ?> Members</h2>
        <div class="memberRank">* denotes a faculty senate member</div>
        <div>
            <?php
            $args = array(
                'post_type' => 'gs_member',
                'posts_per_page' => -1,
                'meta_query' =>
                    array(
                        array(
                            'key'       => $setting_committee,
                            'value'     => $setting_current_year,
                            'compare'   => 'LIKE',
                        )
                    ),
            );
            ?>
            <div>
            <?php
            $member_order = array(
                'chair',
                'vice-chair',
                'liaison from the college of graduate studies',
                'student',
                'ex officio',
                'member',
                '',
            );

            $members = array();
            $query_members = new WP_Query( $args );
            if($query_members->have_posts()):
                while ($query_members->have_posts()):
                    $query_members->the_post();

                    $meta = get_post_meta( $post->ID );
                    array_push( $members, renderMember( $meta, $page_meta_settings, $setting_current_year ) );

                endwhile;
            endif;
            wp_reset_query();

            for( $member_order_counter = 0; $member_order_counter < count( $member_order ); $member_order_counter++ ) {
                $order = $member_order[ $member_order_counter ];
                for( $i = 0; $i < count( $members ); $i++ ) {
                    $member = $members[ $i ];

                    //echo $member['title'] . ' (' . $order . ') ' . ($member['title'] === $order).'<br>';
                    if( $member['title'] === $order )
                        echo $member['card'];

                }
            }

            ?>
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
