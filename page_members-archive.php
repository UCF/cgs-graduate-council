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
                    <div id="years"></div>
                </div>
                <div class="col-xs-9">
                    <div id="members"></div>
                </div>
                <div style="clear: both;"></div>
                <div><?php edit_post_link('Edit Page');?></div>
            </div>
        </div>
    </main><!-- .site-main -->
    <?php
    wp_register_script( 'members-archive', get_template_directory_uri() . '/js/page_members-archive.js' );
    wp_localize_script( 'members-archive', 'settings', array(
        'currentYear' => $setting_current_year,
        'committee' => trim( $setting_committee ),
    ) );
    wp_enqueue_script( 'members-archive' );
    ?>
    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
