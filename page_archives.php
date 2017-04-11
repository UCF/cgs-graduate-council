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
            <div class="col-xs-3">
                <h3>Years</h3>
                <ul id="years_holder" class="list-no-bullet">
                    <?php
                    for( $i = 0, $l = count( $setting_years ); $i < $l; $i++ ) { ?>
                        <li>
                            <label>
                                <input type="checkbox" name="years" onclick="updateFilter( this, 'year', '<?php echo $setting_years[ $i ]; ?>' );"
                                    <?php echo (( $input_current_year == $setting_years[ $i ] )?'selected':''); ?> >
                                <?php echo $setting_years[ $i ]; ?>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
                <h3>Committee</h3>
                <ul id="committees_holder" class="list-no-bullet">
                    <?php
                    $committees = array( 'Appeals', 'Curriculum', 'Policy', 'Program Review' );
                    $committee_values = array( 'appeals_serving_years', 'curriculum_serving_years', 'policy_serving_years', 'program_serving_years' );
                    for( $i = 0, $l = count( $committees ); $i < $l; $i++ ) { ?>
                        <li>
                            <label>
                                <input type="checkbox" name="committee" onclick="updateFilter( this, 'committee', '<?php echo $committee_values[ $i ]; ?>' );"
                                    <?php echo (( $input_council == $committees[ $i ] )?'selected':''); ?> >
                                <?php echo $committees[ $i ]; ?>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
                <h3>Document Types</h3>
                <ul id="documents_holder" class="list-no-bullet">
                    <?php
                    $documentTypes = array( 'Agenda', 'Approved Polices', 'Minutes', 'Forms and Files', 'Reports' );
                    for( $i = 0, $l = count( $documentTypes ); $i < $l; $i++ ) { ?>
                        <li>
                            <label>
                                <input type="checkbox" name="documentTypes" onclick="updateFilter( this, 'document-type', '<?php echo $documentTypes[ $i ]; ?>' )"
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
                        <select onchange="setSortBy( this )">
                            <option value="+title">File name</option>
                            <option value="-year">Years</option>
                            <option value="+committee">Committee</option>
                            <option value="+document-type">Document Type</option>
                        </select>
                    </label>
                </div>
                <h1><?php the_title(); ?></h1>
                <table class="meeting-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Document Types</th>
                            <th>Years</th>
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
                    'order' => 'ASC',
                    'orderby' => 'title'
                );

                $query_files = new WP_Query($args);


                if ($query_files->have_posts()):

                    $json = array();

                    while ($query_files->have_posts()):
                        $query_files->the_post();

                        $meta = get_post_meta( $post->ID );

                        array_push( $json, array(
                            'id'            => $post->ID,
                            'title'         => get_the_title(),
                            'file_url'      => $meta[ 'file_url' ][ 0 ],
                            'committee'     => $meta[ 'committee' ][ 0 ],
                            'document-type' => $meta[ 'document-type' ][ 0 ],
                            'year'          => $meta[ 'year' ][ 0 ],
                        ));

                    endwhile;
                    ?>
                    <script>
                        var files = <?php echo json_encode( $json ); ?>;
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
                    updateFilterList( element.checked, filters[ filterName ], filter );
                    updateFiles( files );
                }
                function filterBy( files, filterBy, filterForList ) {
                    var list = [];

                    for( var i = files.length; i --> 0; )
                        for( var j = filterForList.length; j --> 0; )
                            if( files[ i ][ filterBy ].toLowerCase() == filterForList[ j ].toLowerCase() ) {
                                list.push( files[ i ] );
                                break;
                            }

                    return list;
                }

                function updateFiles( files ){
                    var filtered = files;

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
                        "program_serving_years": "Program Review"
                    };

                    var direction = ( sortFilesBy.charAt(0) == '-')? -1:1;

                    files.sort( sortByGiven( sortFilesBy.substr(1), direction ) );

                    for( var i = files.length; i --> 0; ) {
                        var file = files[ i ];

                        r += "<tr>";
                        r += "<td><a href='" + file.file_url + "'>" + file.title + "</a></td>";
                        r += "<td>" + file['document-type'].charAt(0).toUpperCase() + file['document-type'].substr(1) + "</td>";
                        r += "<td>" + file.year + "</td>";
                        r += "<td>" + programCodeToProgram[ file.committee ] + "</td>";
                        r += "</tr>";
                    }
                    if( !files.length ) {
                        r += "<tr><td colspan='4'>No results</td></tr>";
                    }

                    return r;
                }
                window.onload = function() {
                    updateFiles( files );
                };
            </script>
        <div class="clear"></div>
        <div>
                <?php edit_post_link('Edit Page');?>
            </div>
        </div>
    </main><!-- .site-main -->
    <script>
    </script>
    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
