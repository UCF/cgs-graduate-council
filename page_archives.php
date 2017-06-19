<?php
/**
 * Template Name: Archives
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
get_header(); ?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div class="content-tile">
        <h1 class="entry-title"><?php the_title(); ?></h1>
        <?php
        $title = '';
        $page_meta_settings = array();

        $setting_years = trim( esc_attr( get_option( 'years' ) ) );
        $setting_years = explode( ',', $setting_years );

        $input_file_category = ( isset( $_POST['document'] ) )? $_POST['document']: "";
        $input_council = ( isset( $_POST['committee'] ) )? $_POST['committee']: "";
        $input_current_year = ( isset( $_POST['search_year'] ) )? $_POST['search_year']: "";


        while ( have_posts() ) : the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $page_meta_settings = get_post_meta( $id );

            ?>
        <?php
        endwhile;
        wp_reset_query();

        ?>
            <div class="col-xs-3" style="padding-right:20px;">
                <h2>Filters</h2>
                <h3><span>Year</span></h3>
                <ul id="years_holder" class="list-no-bullet">
                    <?php
                    for( $i = 0, $l = count( $setting_years ); $i < $l; $i++ ) { ?>
                        <li>
                            <label class="archive-filter" id="year-<?php echo $setting_years[ $i ]; ?>">
                                <input type="checkbox" name="years" onclick="updateFilter( this, 'year', '<?php echo $setting_years[ $i ]; ?>' );"
                                    <?php echo (( $input_current_year == $setting_years[ $i ] )?'selected':''); ?> >
                                <?php echo $setting_years[ $i ]; ?>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
                <h3><span>Committee</span></h3>
                <ul id="committees_holder" class="list-no-bullet">
                    <?php
                    $committees = array( 'Appeals', 'Curriculum', 'Policy', 'Program Review' );
                    $committee_values = array( 'appeals_serving_years', 'curriculum_serving_years', 'policy_serving_years', 'program_serving_years' );
                    for( $i = 0, $l = count( $committees ); $i < $l; $i++ ) { ?>
                        <li>
                            <label class="archive-filter" id="committee-<?php echo $committee_values[ $i ]; ?>">
                                <input type="checkbox" name="committee" onclick="updateFilter( this, 'committee', '<?php echo $committee_values[ $i ]; ?>' );"
                                    <?php echo (( $input_council == $committees[ $i ] )?'selected':''); ?> >
                                <?php echo $committees[ $i ]; ?>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
                <h3><span>Document Type</span></h3>
                <ul id="documents_holder" class="list-no-bullet">
                    <?php
                    $documentTypes = array( 'Agenda', 'Approved Policies', 'Minutes', 'Forms and Files', 'Reports' );
                    $documentTypes_values = array( 'agenda', 'polices', 'minutes', 'forms', 'reports' );
                    for( $i = 0, $l = count( $documentTypes ); $i < $l; $i++ ) { ?>
                        <li>
                            <label class="archive-filter" id="documentType-<?php echo $documentTypes_values[ $i ]; ?>">
                                <input type="checkbox" name="documentTypes" onclick="updateFilter( this, 'document-type', '<?php echo $documentTypes_values[ $i ]; ?>' )"
                                    <?php echo (( $input_council == $documentTypes[ $i ] )?'selected':''); ?> >
                                <?php echo $documentTypes[ $i ]; ?>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="col-xs-9">
                <div style="float:right; margin-top: 10px;">
                    <label>
                        Sort By:
                        <select class="archive-sort" onchange="setSortBy( this );dataLayer.push({'event': 'archive-sort-change'});">
                            <option value="+title">File name</option>
                            <option value="-year">Year</option>
                            <option value="+committee">Committee</option>
                            <option value="+document-type">Document Type</option>
                        </select>
                    </label>
                </div>
                <h2>Results</h2>
                <table class="meeting-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Document Type</th>
                            <th>Year</th>
                            <th>Committee</th>
                        </tr>
                    </thead>
                    <tbody id="files-holder">
                        <tr><td colspan="4">No results</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="clear"></div>
            <div>
                <?php edit_post_link('Edit Page');?>
            </div>
        </div>
    </main><!-- .site-main -->
    <?php
    wp_register_script( 'page-archive', get_template_directory_uri() . '/js/page_archive.js' );
    wp_enqueue_script( 'page-archive' );
    ?>
    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
