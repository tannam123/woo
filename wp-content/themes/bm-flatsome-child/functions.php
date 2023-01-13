<?php
// Add custom Theme Functions here

add_action( 'wp_enqueue_scripts', 'salesgen_theme_enqueue_styles' );
function salesgen_theme_enqueue_styles() {
	wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		'flatsome-main',
		wp_get_theme()->get('Version').'.'.rand(1,9999)
	);
}


add_filter( 'woocommerce_product_tabs', 'salesgen_remove_product_tabs', 98 );

function salesgen_remove_product_tabs( $tabs ) {
	if(is_product()){
		unset( $tabs['additional_information'] );   // Remove the additional information tab
		unset( $tabs['reviews'] );   // Remove the additional information tab
	}
	return $tabs;
}

// remove related_products
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products' );



//add_filter( 'woocommerce_product_related_posts_force_display', 'salesgen_product_related_posts_force_display', 99, 2 );

function salesgen_product_related_posts_force_display($re, $prod){
	return true;
}

add_filter( 'woocommerce_product_tabs', 'salesgen_new_product_tab' );
function salesgen_new_product_tab( $tabs ) {
// Adds the new tab
	$tabs['shipping_tab'] = array(
		'title'     => __( 'Shipping & Manufacturing Info', 'woocommerce' ),
		'priority'  => 10,
		'callback'  => 'salesgen_new_product_shipping_tab_content'
	);

	return $tabs;
}

function salesgen_new_product_shipping_tab_content(){
	echo do_shortcode( '[block id="shipping"]' );
}

add_action( 'woocommerce_after_single_product_summary', 'salesgen_reviews', 30 );

function salesgen_reviews(){
echo comments_template();
	
}

//force terms checkbox on checkout
add_action( 'template_redirect', 'salesgen_remove_my_action', 99 );
function salesgen_remove_my_action(){
	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );
	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
	remove_action( 'woocommerce_after_cart_table', 'woocommerce_cross_sell_display' );
	remove_action( 'flatsome_product_box_actions', 'flatsome_lightbox_button' );
	add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 9 );

	//coupons
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_coupon_form', 30 );

	//payments
	remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

	add_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment', 90 );
	add_action( 'woocommerce_checkout_after_customer_details', 'salesgen_custom_heading_payment', 89 );

	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );
	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
	remove_action( 'woocommerce_checkout_after_order_review', 'wc_checkout_privacy_policy_text', 1 );


	add_action( 'woocommerce_checkout_after_customer_details', 'wc_checkout_privacy_policy_text', 91 );
	add_action( 'woocommerce_checkout_after_customer_details', 'wc_terms_and_conditions_page_content', 92 );


	add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );
	//modify filter position  
	remove_action( 'flatsome_category_title_alt', 'wc_setup_loop' );
	remove_action( 'flatsome_category_title_alt', 'woocommerce_result_count', 20 );
	remove_action( 'flatsome_category_title_alt', 'woocommerce_catalog_ordering', 30 );
	remove_action( 'flatsome_category_title', 'flatsome_shop_loop_tools_breadcrumbs', 10 );

	add_action( 'flatsome_category_filter', 'wc_setup_loop' );
	add_action( 'flatsome_category_filter', 'woocommerce_result_count', 20 );
	add_action( 'flatsome_category_filter', 'woocommerce_catalog_ordering', 30 );
	add_action( 'flatsome_category_breadcumb', 'flatsome_shop_loop_tools_breadcrumbs', 10 );
  
	
	//categroy description
	remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
	remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );

	add_action( 'flatsome_products_after', 'salesgen_products_archive_desc', 10 );

	add_action('flatsome_category_sublist', 'sg_display_subcategories_list', 10 ); 
	
	
add_filter( 'woocommerce_product_related_posts_relate_by_category', 'salesgen_product_related_posts_relate_by_category', 999, 2 );
add_filter( 'woocommerce_product_related_posts_relate_by_tag', 'salesgen_product_related_posts_relate_by_tag', 999, 2 );

function salesgen_product_related_posts_relate_by_category($t, $p){
	return false;
}
function salesgen_product_related_posts_relate_by_tag($t, $p){
	return true;
}
}

function salesgen_products_archive_desc(){
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		if ( $paged > 1) {
			return;
		}
		woocommerce_taxonomy_archive_description();
		woocommerce_product_archive_description();
	
	
}

// Remove Order Notes Field
add_filter( 'woocommerce_checkout_fields' , 'remove_order_notes' );

function remove_order_notes( $fields ) {
     unset($fields['order']['order_comments']);
     return $fields;
}

function salesgen_custom_heading_payment(){
?>
<div id="checkout_custom_heading"><div style="flex:none;"><h3 style="padding-top:0;">Payment Info</h3></div><div class="ta-right"><img class="ssl-secu perfmatters-lazy loaded" src="https://cdn.32pt.com/public/sl-retail/assets/logos/ssl-seal.svg" alt="Checkout 1" title="Checkout 1" width="48" height="22" data-src="https://cdn.32pt.com/public/sl-retail/assets/logos/ssl-seal.svg" data-was-processed="true"><noscript><img class="ssl-secu"
	src="https://cdn.32pt.com/public/sl-retail/assets/logos/ssl-seal.svg" alt="Checkout 1" title="Checkout 1" width="48" height="22"></noscript>
	<img class="norton-secu perfmatters-lazy loaded" src="https://i1.wp.com/cdn.32pt.com/public/sl-retail/assets/logos/norton-seal.png?resize=53%2C30&amp;ssl=1" alt="Checkout 2" title="Checkout 2" width="53" height="30" data-src="https://i1.wp.com/cdn.32pt.com/public/sl-retail/assets/logos/norton-seal.png?resize=53%2C30&amp;ssl=1" data-was-processed="true"><noscript><img class="norton-secu" src="https://i1.wp.com/cdn.32pt.com/public/sl-retail/assets/logos/norton-seal.png?resize=53%2C30&#038;ssl=1" alt="Checkout 2"
	title="Checkout 2" width="53" height="30"  data-recalc-dims="1"></noscript></div></div>
<?php
}

/*
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
add_action( 'woocommerce_after_shop_loop_item_title', 'min_price_variant', 10 );
function min_price_variant(){
	global $product;
	if ( $product ) {
		if ( $product->get_type() == 'variable'){
			$min_price = $product->get_variation_price(); // Min active price
			echo wc_price($min_price) . ' <span class="woocommerce-Price-amount amount"><bdi>USD</bdi></span>';
		} else echo $product->get_price_html() . ' <span class="woocommerce-Price-amount amount"><bdi>USD</bdi></span>';
	}
	
}


add_filter('woocommerce_get_price_suffix', 'sn_get_price_suffix', 999);
function sn_get_price_suffix($suffix) {
	return $suffix . ' <span class="woocommerce-Price-amount amount"><bdi>USD</bdi></span>';
}
*/


add_filter('woocommerce_get_price_html', 'sn_hide_variation_price', 99, 2);
function sn_hide_variation_price( $v_price, $v_product ) {
	$v_product_types = array( 'variable');
	//echo $v_price;
	if ( in_array ( $v_product->product_type, $v_product_types ) && is_product() && (strpos($v_price, "&ndash;") !== false) ) {
		return '';
	}
	// return regular price
	return $v_price;
}


add_action( 'woocommerce_after_variations_table', 'woocommerce_single_variation_add_to_cart_button', 20 );

//add_action( 'init', 'remove_sn_actions' );

function remove_sn_actions() {
    remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);
}


// Wrapper quantity input & add to cart button - start

add_action('woocommerce_before_add_to_cart_quantity', 'sg_add_to_cart_quantity_wrapper_open', 2);
add_action('woocommerce_after_add_to_cart_button', 'sg_add_to_cart_quantity_wrapper_close', 999);
function sg_add_to_cart_quantity_wrapper_open() {
	echo '<div class="sg_wrapper_add_to_cart_quantity">';
}

function sg_add_to_cart_quantity_wrapper_close() {
	echo '</div>';
}
// Wrapper quantity input & add to cart button - end


// Displaying the subcategories after category title

function sg_display_subcategories_list() {
	
    if ( is_product_category() ) {
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		if ( $paged > 1) {
			return;
		}
        $term_id  = get_queried_object_id();
        $taxonomy = 'product_cat';

        // Get subcategories of the current category
        $terms    = get_terms([
            'taxonomy'    => $taxonomy,
            'hide_empty'  => false,
            'parent'      => $term_id
        ]);
      
        if ( count($terms) > 0 ) {
            $ids = array();
            foreach ( $terms as $term ) {
            $ids[] = $term->term_id;
            }
          
			echo do_shortcode('[gap height="10px"][ux_product_categories style="push" type="row" col_spacing="normal" columns="6" ids="'.implode(',',$ids).'"]');

        }
    }
}




/// Custom from code cua anh Hoa
/**
 * Add custom data attribute to every image element
 */
add_filter( 'wp_get_attachment_image_attributes', 'add_custom_image_data_attributes', 22, 2 );
function add_custom_image_data_attributes( $attr, $attachment) {
    if ( get_post_type() == 'product' ) {
        $text_value = get_the_title();

        $attr['alt'] = $text_value;
    $attr['title'] = $text_value;
    }

    return $attr;
}


// short code hieen thi tags trong description
add_shortcode('thien_display_title', 'podcustome_display_title');
function podcustome_display_title(){
    global $product;
    return $product->get_name();
}


// short code hieen thi tags trong description
add_shortcode('thien_display_tags', 'podcustome_display_tags');
function podcustome_display_tags(){
    global $product;
    $output = array();

    if (isset($product)) {
        $terms = wp_get_post_terms(get_the_id(), 'product_tag');
        foreach( $terms as $term) {
            $term_link = get_term_link( $term );
            $output[] = '<a href="' . esc_url( $term_link ) . '">' . $term->name . '</a>';//$term_name;
        }
        return implode(", ", $output);
    }
    return;
}


// Shortcode hien thi anh attachment trong description
add_shortcode('thien_display_attachment_images', 'podcustome_display_attachment_images');
function podcustome_display_attachment_images() {
    global $product;

    if (isset($product)){
        $output = array();
        $title = $product->get_name();
        $image_id = $product->get_image_id();
        $image_src = wp_get_attachment_url($image_id);
        $html = '<img class="size-medium wp-image-287 aligncenter" src="'.$image_src.'" alt="'.$title.'" title="'.$title.'" width="650" height="650" />';
        $output[] = $html;

        $attachment_ids = $product->get_gallery_attachment_ids();

        foreach($attachment_ids as $attachment_id){
            $image_src = wp_get_attachment_url($attachment_id);

            $html = '<img class="size-medium wp-image-287 aligncenter" src="'.$image_src.'" alt="'.$title.'" title="'.$title.'" width="650" height="650" />';
            // $html = wp_get_attachment_image($attachment_id);
            $output[] = $html;
        }
        return implode(",",$output);
    }
    return;
}


add_action('wp_footer', function(){?>
    <script>
    function radio_checked(){
        jQuery.each(jQuery('div input[type="radio"]:checked'),function(){
        var label=jQuery(this).siblings('label').text();
        var help=jQuery(this).parent('div').parent('div');
        console.log(help.siblings('.wcpa_helptext').length);
        if(help.siblings('.wcpa_helptext').length==0){
            help.before(('<span class="wcpa_helptext">'+label+'</span>'));
        }
        else{
            help.siblings('.wcpa_helptext').text(label);
        }
    });
    }
    jQuery(document).ready(function(){
        radio_checked();
    });
    jQuery('div input[type="radio"]').change(function(){
        radio_checked();
    });
    </script>
<?php  });