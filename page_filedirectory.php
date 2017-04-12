<?php
/**
 * Template Name: File Directory
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div class="content-tile">
            <?php

            $setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );
            $setting_file_category = '';
            $setting_council = '';
            $setting_current_year_only = '';

            while ( have_posts() ) : the_post();
                $id = get_the_ID();

                $meta = get_post_meta( $id );
                $setting_file_category = $meta['document-type'][0];
                $setting_council = $meta['committee'][0];
                $setting_current_year_only = $meta['current_year_only'][0];
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
                    'post_type' => 'gs_file',
                    'posts_per_page' => -1,
                    'meta_query' =>
                        array(
                            'relation' => 'AND',
                            array(
                                'key'       => 'document-type',
                                'value'     => $setting_file_category,
                                'compare'   => '=',
                            ),
                            array(
                                'key'       => 'committee',
                                'value'     => $setting_council,
                                'compare'   => '=',
                            ),
                        ),
					'order' => 'ASC',
					'orderby' => 'title',
                );

                if( $setting_current_year_only == 'on' )
                    array_push( $args['meta_query'], array(
                        'key'       => 'year',
                        'value'     => $setting_current_year,
                        'compare'   => 'LIKE',
                    ) );

                $no_files = array(
                    '' => 'No files found',
                    'agenda' => 'No agendas have been published this academic year.',
                    'minutes' => 'No minutes have been published this academic year.',
                    'forms' => 'No forms have been published this academic year.',
                    'reports' => 'No reports have been published this academic year.',
                    'polices' => 'No policies have been approved during this academic year.',
                );

                $query_files = new WP_Query( $args );
                if($query_files->have_posts()):
                    echo "<ul>";
                while ($query_files->have_posts()):
                    $query_files->the_post();

                    $meta = get_post_meta( $post->ID );
                ?>
                    <li><a href="<?php echo $meta['file_url'][0]; ?>"><?php echo get_the_title(); ?></a> - <?php edit_post_link('Edit File');?></li>
                <?php

                endwhile;
                    echo "</ul>";
                else:
                    echo $no_files[ $setting_file_category ];
                endif;
                wp_reset_query();
                ?>
                <div>
                    <?php edit_post_link('Edit Page');?>
                </div>
            </div>
        </div>
    </main><!-- .site-main -->

    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
