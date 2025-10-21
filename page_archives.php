<?php
/**
 * Template Name: Archives
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
 
 $setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );
 
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

        // Taxonomy filters and sanitized values for use in filters
        $all_tax_terms = [];
        foreach (get_object_taxonomies('gs_file', 'objects') as $taxonomy_slug => $taxonomy_obj) {
            $tax_args = array(
                'taxonomy'   => $taxonomy_slug,
                'hide_empty' => false,
                'orderby'    => 'name',
            );
            if ('committee-year' == $taxonomy_slug) $tax_args['order'] = 'DESC';
            $tax_names = array_map(
                function($term) {return esc_html($term->name);},
                get_terms( $tax_args )
            );
            $tax_values = array_map(
                function($term) {return 'value-' . sanitize_title($term);},
                $tax_names
            );
            $tax_term_ids = array_map(
                function($term) {return $term->term_id;},
                get_terms( $tax_args )
            );

            $all_tax_terms[$taxonomy_slug] = array('label' => esc_html($taxonomy_obj->labels->singular_name), 'names' => $tax_names, 'values' => $tax_values, 'term_ids' => $tax_term_ids);
        }

        // Get all committees' current year setting
        $setting_committee_current_year = [];
        foreach ($all_tax_terms['committee']['names'] as $index => $committee) {
            $option_key = 'current_year_' . $all_tax_terms['committee']['term_ids'][$index];
            $setting_committee_current_year[$committee] = trim( esc_attr( get_option( $option_key ) ) );
        }
        var_dump($setting_committee_current_year);
        var_dump($all_tax_terms['committee-year']);

        // Render function for taxonomy filters
        function render_tax_filter_checkboxes($term, $term_array, $current_terms) {
            ?>
                <h3><span><?= $term_array['label'] ?></span></h3>
                <ul id="<?= esc_html($term) ?>-holder" class="list-no-bullet">
                    <?php
                    for( $i = 0, $l = count( $term_array['names'] ); $i < $l; $i++ ) { ?>
                        <li>
                            <label class="archive-filter" id="<?php echo esc_html($term) . '-' . $term_array['values'][ $i ]; ?>">
                                <input type="checkbox" name="<?= esc_html($term) ?>" onclick="updateFilter( this, '<?= 'tax-' . esc_html($term) ?>', '<?php echo $term_array['values'][ $i ]; ?>' );"
                                    <?php echo ( is_array($current_terms) && in_array(trim( $term_array['values'][ $i ] ), $current_terms ) ?'checked':''); ?> >
                                <?php echo $term_array['names'][ $i ]; ?>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
            <?php
        }

        // When would this page be retrieved with a POST request?
        $input_file_category = ( isset( $_POST['document'] ) )? $_POST['document']: "";
        $input_council = ( isset( $_POST['committee'] ) )? $_POST['committee']: "";
        $input_current_year = ( isset( $_POST['search_year'] ) )? $_POST['search_year']: $setting_current_year;



        while ( have_posts() ) : the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $page_meta_settings = get_post_meta( $id );

            ?>
        <?php
        endwhile;
        wp_reset_query();

        ?>
            <div class="col-xs-12 col-sm-3" style="padding-right:20px;">
                <h2>Filters</h2>
                <?php 
                    foreach($all_tax_terms as $taxonomy_slug => $taxonomy_obj) {
                        render_tax_filter_checkboxes($taxonomy_slug, $taxonomy_obj, $setting_committee_current_year);
                     }
                ?>
                <h3><span>Year</span></h3>
                <ul id="years_holder" class="list-no-bullet">
                    <?php
                    for( $i = 0, $l = count( $setting_years ); $i < $l; $i++ ) { ?>
                        <li>
                            <label class="archive-filter" id="year-<?php echo $setting_years[ $i ]; ?>">
                                <input type="checkbox" name="years" onclick="updateFilter( this, 'year', '<?php echo $setting_years[ $i ]; ?>' );"
                                    <?php //echo (( trim( $input_current_year ) == trim( $setting_years[ $i ] ) ) ?'checked':''); ?> >
                                <?php echo $setting_years[ $i ]; ?>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
                <h3><span>Committee</span></h3>
                <ul id="committees_holder" class="list-no-bullet">
                    <?php
                    $committees = array( 'Appeals', 'Curriculum', 'Policy', 'Program Review and Awards' );
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
                    $documentTypes = array( 'Agenda', 'Policies', 'Minutes', 'Forms and Files', 'Reports' );
                    $documentTypes_values = array( 'agenda', 'policies', 'minutes', 'forms', 'reports' );
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
            <div class="col-xs-12 col-sm-9">
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
                <?php
                $args = array(
                    'post_type' => 'gs_file',
                    'posts_per_page' => -1,
                    'post_status ' => 'published',
                    'order' => 'ASC',
                    'orderby' => 'title'
                );

                $query_files = new WP_Query($args);


                if ($query_files->have_posts()):

                    $json = array();
                    $json_filters = array_map(function($term) {return 'tax-' . $term;}, array_keys($all_tax_terms));

                    while ($query_files->have_posts()):
                        $query_files->the_post();
                        $meta = get_post_meta( $post->ID );

                        // Pulled attributes array out of json array push to allow for taxonomies
                        $file_attributes = [];
                        $policy_status = array_key_exists('policy-status', $meta) ? $meta[ 'policy-status' ] : "";
                        $file_attributes = array(
                            'id'            => $post->ID,
                            'title'         => get_the_title(),
                            'file_url'      => $meta[ 'file_url' ][ 0 ],
                            'committee'     => $meta[ 'committee' ][ 0 ],
                            'document-type' => $meta[ 'document-type' ][ 0 ],
                            'year'          => $meta[ 'year' ][ 0 ],
                            'policy-status' => $policy_status,
                        );

                        foreach($all_tax_terms as $taxonomy_slug => $taxonomy_obj) {
                            $file_terms = get_the_terms($post->ID, $taxonomy_slug);
                            if (!$file_terms) $file_terms = [];
                            $file_terms_values = array_map(
                                function($term) {return esc_html($term->name);},
                                $file_terms
                            );
                            $attribute_key = 'tax-' . $taxonomy_slug;
                            $file_attributes[$attribute_key] = $file_terms_values;
                            $file_terms_values = array_map(
                                function($term) {return 'value-' . sanitize_title(esc_html(strtolower($term->name)));},
                                $file_terms
                            );
                            $attribute_key = 'tax-' . $taxonomy_slug . '-value';
                            $file_attributes[$attribute_key] = $file_terms_values;
                        }                        

                        array_push( $json, $file_attributes);

                    endwhile;
                    ?>
                    <script>
                        var files = <?php echo json_encode( $json ); ?>;
                        var filter_terms = <?php echo json_encode( $json_filters ); ?>;
                    </script>
                    <?php
                endif;
                wp_reset_query();
                ?>
            </div>
            <script>
                var $html = {
                    years: document.getElementsByName( "years" ),
                    committees: document.getElementsByName( "committee" ),
                    documentTypes: document.getElementsByName( "documentTypes" ),
                    files_holder: document.getElementById( "files-holder" )
                };

                var tax_filters = Object.fromEntries(filter_terms.map((key) => [key, []]));
                var filters = {
                    "year": [],
                    "committee": [],
                    "document-type": []
                };
                var sortFilesBy = '+title';

                function setSortBy( element ) {

                    sortFilesBy = element.value;

                    updateFiles( files );
                }

                function sortByGiven( given, direction ) {
                    if( direction == undefined )
                        direction = 1;
                    return function( a, b) {
                        if (a[given] > b[given])
                            return -direction;
                        if (a[given] < b[given])
                            return direction;
                        return 0;
                    }
                }

                function updateFilterList( condition, list, item ) {
                    if( condition ) { // Add to the list

                        if( list.indexOf( item ) > -1 ) // Item already exists
                            return ;

                        list.push( item ); // Push non-existing item

                    } else {
                        var index = list.indexOf( item );

                        if( index > -1 ) // Item exists
                            list.splice( index, 1 );
                    }
                }
                function updateFilter( element, filterName, filter ) {
                    if (filterName.includes("tax-")) {
                        updateFilterList( element.checked, tax_filters[ filterName ], filter );
                    }
                    else {
                        updateFilterList( element.checked, filters[ filterName ], filter );
                    }
                    updateFiles( files );
                }
                function tax_filterBy( files, filterBy, filterForList ) {
                    var list = [];
                    var tax_filterBy = filterBy + '-value';

                    for( var i = files.length; i --> 0; ) {
                        for( var j = filterForList.length; j --> 0; ) {
                            if ( Array.isArray(files[ i ][ tax_filterBy ]) && files[ i ][ tax_filterBy ].includes(filterForList[ j ].toLowerCase()) ) {
                                list.push( files[ i ] );
                                break;
                            }
                        }
                    }

                    return list;
                }
                function filterBy( files, filterBy, filterForList ) {
                    var list = [];

                    for( var i = files.length; i --> 0; ) {
                        for( var j = filterForList.length; j --> 0; ) {
                            if ( files[ i ][ filterBy ].toLowerCase() == filterForList[ j ].toLowerCase() ) {
                                list.push( files[ i ] );
                                break;
                            }
                        }
                    }

                    return list;
                }

                function updateFiles( files ){
                    var filtered = files;

                    for( var filterName in tax_filters )
                        if( tax_filters[ filterName ].length )
                            filtered = tax_filterBy( filtered, filterName, tax_filters[ filterName ] );
                    for( var filterName in filters )
                        if( filters[ filterName ].length )
                            filtered = filterBy( filtered, filterName, filters[ filterName ] );

                    $html.files_holder.innerHTML = renderFiles( filtered );
                }
                function renderFiles( files ) {
                    var r = '';

                    var programCodeToProgram = {
                        "appeals_serving_years": "Appeals",
                        "curriculum_serving_years": "Curriculum",
                        "policy_serving_years": "Policy",
                        "program_serving_years": "Program Review and Awards"
                    };

                    var displayFileType = {
                        'agenda': 'Agenda',
                        'minutes': 'Minutes',
                        'policies': 'Policies',
                        'forms': 'Forms and Files',
                        'reports': 'Reports'
                    };

                    var direction = ( sortFilesBy.charAt(0) == '-')? -1:1;

                    files.sort( sortByGiven( sortFilesBy.substr(1), direction ) );

                    for( var i = files.length; i --> 0; ) {
                        var file = files[ i ];

						var filter_type = displayFileType[ file['document-type'] ];
                        var document_year = (!Array.isArray(file['tax-committee-year']) || 0 == file['tax-committee-year'].length) ? file.year : file['tax-committee-year'].join(", ");
                        if ( file['policy-status'] && 'Policies' == filter_type ) filter_type = file['policy-status'] + ' Policy';
						var program_type = (!Array.isArray(file['tax-committee']) || 0 == file['tax-committee'].length) ? programCodeToProgram[ file.committee ] : file['tax-committee'].join(", ");

                        r += "<tr>";
                        r += "<td><a href='" + file.file_url + "'>" + file.title + "</a></td>";
                        r += "<td>" + ((filter_type)?filter_type:'') + "</td>";
                        r += "<td>" + document_year + "</td>";
                        r += "<td>" + ((program_type)?program_type:'') + "</td>";
                        r += "</tr>";
                    }
                    if( !files.length ) {
                        r += "<tr><td colspan='4'>No results</td></tr>";
                    }

                    return r;
                }
                window.onload = function() {
					//updateFilter( { checked: true }, 'year', '<?php //echo $input_current_year; ?>' );
                    <?php foreach ($setting_committee_current_year as $committee_year) {
                        echo "updateFilter( { checked: true }, 'tax-committee-year', '" . $committee_year . "' );\n";
                    }?>
                };
            </script>
            <div class="clear"></div>
            <div>
                <?php edit_post_link('Edit Page');?>
            </div>
        </div>
    </main><!-- .site-main -->
    <?php get_sidebar( 'content-bottom' ); ?>
</div><!-- .content-area -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
