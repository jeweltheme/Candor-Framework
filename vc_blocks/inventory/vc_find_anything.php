<?php 

/**
 * The Shortcode
 */
function candor_framework_inventory_find_anything_shortcode( $atts ) {
	extract( 
		shortcode_atts( 
			array(
					'title'           => 'Find Anything you Want',
					'subtitle'        => 'Appropriately Strategize Performance Based Intellectual Capital Before Premier Users',
					'pppage' 		  => '8',
					//'style' 		  => 'style1',
					'orderby'         => 'name',
					'categories_slug' => 'arts',
					'filter' 		  => 'all'
			), $atts 
		) 
	);
	

		$term_list              = array();
		$custom_category_labels = array();

		//first let's do only one query and get all the terms - we will reuse this info to avoid multiple queries
		$query_args = array( 'order' => 'DESC', 'hide_empty' => false, 'hierarchical' => true, 'pad_counts' => true );
		if ( ! empty( $orderby ) && is_string( $orderby ) ) {
			$query_args['orderby'] = $orderby;
		}

		$all_terms = get_terms(
			'job_listing_category',
			$query_args
		);

		//bail if there was an error
		if ( is_wp_error( $all_terms ) ) {
			return;
		}

		//now create an array with the category slug as key so we can reference/search easier
		$all_categories = array();
		foreach ( $all_terms as $key => $term ) {
			$all_categories[ $term->slug ] = $term;
		}


		//if we have received a list of categories to display (their slugs and optional label), use that
		if ( ! empty( $categories_slug ) && is_string( $categories_slug ) ) {
			$categories = explode( ',', $categories_slug );
			foreach ( $categories as $key => $category ) {
				if ( strpos( $category, '(' ) !== false ) {
					$category  = explode( '(', $category );
					$term_slug = trim( $category[0] );

					if ( substr( $category[1], - 1, 1 ) == ')' ) {
						$custom_category_labels[ $term_slug ] = trim( substr( $category[1], 0, - 1 ) );
					}

					if ( array_key_exists( $term_slug, $all_categories ) ) {
						$term_list[] = $all_categories[ $term_slug ];
					}
				} else {
					$term_slug = trim( $category );
					if ( array_key_exists( $term_slug, $all_categories ) ) {
						$term_list[] = $all_categories[ $term_slug ];
					}
				}
			}

			//now if the user has chosen to sort these according to the number of posts, we should do that
			// since we will, by default, respect the order of the categories he has used
			if ( 'count' == $orderby ) {
				// Define the custom sort function
				function sort_by_post_count( $a, $b ) {
					return $a->count < $b->count;
				}

				// Sort the multidimensional array
				usort( $term_list, "sort_by_post_count" );
			} elseif ( 'rand' == $orderby ) {
				//randomize things a bit if this is what the user ordered
				shuffle( $term_list );
			}

		} else {
			//it seems we will have to figure out ourselves what categories to display

			if ( ! $pppage = intval( $pppage ) ) {
				$pppage = 4;
			}

			$term_list = array_slice( $all_categories, 0, $pppage );
		}



	ob_start();

	if ( ! empty( $term_list ) ) { ?>


		<div class="container padd-lr0">
		    <div class="row">
		        <div class="col-xs-12">
		            <header class=" inv-block-header margin-lg-t140 margin-lg-b100 ">
		                <h3><?php echo $title;?></h3>
		                <?php if ( ! empty( $subtitle ) ) { ?>
		                	<span><?php echo $subtitle; ?></span>
		                <?php } ?>
		            </header>
		        </div>
		    </div>
		    <div class="row">
		        <div class="col-xs-12 padd-lr0">
		            <div class="inv-categorys margin-lg-b120">
		            		
		            		<?php 
		            			$i=1;
		            			$j = 9;
			            		foreach ( $term_list as $key => $term ) {
					            	if ( ! $term ) {
					            		continue;
					            	}
					            	$icon_url           = inventory_get_term_icon_url( $term->term_id );
					            	$image_url          = inventory_get_term_image_url( $term->term_id, 'inventory-listing-thumb' );
					            	$attachment_id      = inventory_get_term_icon_id( $term->term_id );


					            	$image_src          = '';

					            	if ( ! empty( $image_url ) ) {

					            		$image_src = $image_url;
					            	} else {
					            		$thumbargs    = array(
					            			'posts_per_page' => 1,
					            			'post_type'      => 'job_listing',
					            			'meta_key'       => 'main_image',
					            			'orderby'          => 'rand',
					            			'tax_query'      => array(
					            				array(
					            					'taxonomy' => 'job_listing_category',
					            					'field'    => 'name',
					            					'terms'    => $term->name
					            					),
					            				)
					            			);
					            		$latest_thumb = new WP_Query( $thumbargs );

					            		if ( $latest_thumb->have_posts() ) {
											//get the first image in the listing's gallery or the featured image, if present
					            			$image_ID  = inventory_get_post_image_id( $latest_thumb->post->ID );
					            			$image_src = '';
					            			if ( ! empty( $image_ID ) ) {
					            				$image     = wp_get_attachment_image_src( $image_ID, 'medium' );
					            				$image_src = $image[0];
					            			}
					            		}
			            	} 

?>

			                <div class="col-xs-12 col-sm-3">
			                    <div class="inv-category-item">
			                        <a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
			                            <div class="inv-category-head bg<?php echo ( $i!=7 || $i!=8 || $i!=14 )?"$j":"$i";?>">
			                            	<img src="<?php echo $image_url; ?>" alt="<?php the_title_attribute();?>">
			                                <div class="inv-elem fa fa-university ">
			                                	<i class="bg<?php echo ( $i!=7 || $i!=8 || $i!=14 )?"$j":"$i";?>"><?php echo $term->count; ?></i>
			                                </div>
			                            </div>
			                            <div class="inv-category-footer">
			                                <span><?php echo isset( $custom_category_labels[ $term->slug ] ) ? $custom_category_labels[ $term->slug ] : $term->name; ?></span>
			                            </div>
			                        </a>
			                    </div>
			                </div>
			            <?php $i++; $j++; } ?>


		            </div>
		        </div>
		    </div>
		</div>


<?php	
}
	wp_reset_postdata();
	
	$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
}
add_shortcode( 'inventory_find_anything', 'candor_framework_inventory_find_anything_shortcode' );

/**
 * The VC Functions
 */
function candor_framework_inventory_find_anything_shortcode_vc() {
	
	vc_map( 
		array(
			"icon" => 'inventory-vc-block',
			"name" => __("Find Anything", 'inventory'),
			"base" => "inventory_find_anything",
			"category" => esc_html__('Inventory WP Theme', 'inventory'),
			'description' => 'Show Most Popular Listings Places',
			"params" => array(

				array(
					"type" => "textfield",
					"heading" => __("Title", 'inventory'),
					"param_name" => "title",
					"value" => 'Find Anything you Want'
				),				

				array(
					"type" => "textfield",
					"heading" => __("Sub Title", 'inventory'),
					"param_name" => "subtitle",
					"value" => 'Appropriately Strategize Performance Based Intellectual Capital Before Premier Users'
				),				

				array(
					"type" => "textfield",
					"heading" => __("Show How Many Posts?", 'inventory'),
					"param_name" => "pppage",
					"value" => '8'
				),
				array(
					"type" => "dropdown",
					"heading" => __("Order By", 'inventory'),
					"param_name" => "orderby",
					"value" => array(
							'Name' => 'name',
							'ID' => 'ID',
							'Title' => 'title',
							'Date' => 'date',
							'Rand' => 'rand',
						),
					),				
				// array(
				// 	"type" => "dropdown",
				// 	"heading" => __("Layout", 'inventory'),
				// 	"param_name" => "style",
				// 	"value" => array(
				// 		'Style 1' => 'style1',
				// 		'Style 2' => 'style2'
				// 		),
				// 	),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Category Slug', 'inventory' ),
					'param_name' => 'categories_slug',
					'value'		  => '',
					'description' => esc_html__( 'List of Listing Categories by slug, Spearated by Comma. Example: arts, music, hotel etc..', 'inventory' ),
				),
			)
		) 
	);
	
}
add_action( 'vc_before_init', 'candor_framework_inventory_find_anything_shortcode_vc');