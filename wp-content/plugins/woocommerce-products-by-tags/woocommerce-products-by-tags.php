<?php
/**
 * Plugin Name: WooCommerce - List Products by Tags
 * Plugin URI: http://www.remicorson.com/list-woocommerce-products-by-tags/
 * Description: List WooCommerce products by tags using a shortcode, ex: [woo_products_by_tags tags="shoes,socks"]
 * Version: 1.0
 * Author: Remi Corson
 * Author URI: http://remicorson.com
 * Requires at least: 3.5
 * Tested up to: 3.5
 *
 * Text Domain: -
 * Domain Path: -
 *
 */

/*
 * List WooCommerce Products by tags
 *
 * ex: [woo_products_by_tags tags="shoes,socks"]
 */
function woo_products_by_tags_shortcode( $atts, $content = null ) {
    // Get attribuets
    extract(shortcode_atts(array(
        "tags" => ''
    ), $atts));
    ob_start();

    $args = array(
        'number'     => $number,
        'orderby'    => $orderby,
        'order'      => $order,
        'hide_empty' => $hide_empty,
        'include'    => $ids
    );
    $loop = new WP_Query( $args );

    $product_categories = get_terms( 'product_cat', $args );

    $count = count($product_categories);
    if ( $count > 0 ){
        echo "<ul>";
        foreach ( $product_categories as $product_category ) {
            echo '<li><a href="' . get_term_link( $product_category ) . '">' . $product_category->name . '</li>';

        }
        echo "</ul>";
    }
    return ob_get_clean();

//
//    // Get attribuets
//    extract(shortcode_atts(array(
//        "tags" => ''
//    ), $atts));
//
//    ob_start();
//
//    // Define Query Arguments
//    $args = array(
//        'post_type' 	 => 'product',
//        'posts_per_page' => 10,
//        'product_tag' 	 => $tags
//    );

//    // Create the new query
//    $loop = new WP_Query( $args );
//
//    // Get products number
//    $product_count = $loop->post_count;
//
//    // If results
//    if( $product_count > 0 ) :
//
//        echo '<ul class="products">';
//
//        // Start the loop
//        while ( $loop->have_posts() ) : $loop->the_post(); global $product;
//
//            global $post;
//
//            echo "<p>" . $thePostID = $post->post_title. " </p>";
//
//            if (has_post_thumbnail( $loop->post->ID ))
//                echo  get_the_post_thumbnail($loop->post->ID, 'shop_catalog');
//            else
//                echo '<img src="'.$woocommerce->plugin_url().'/assets/images/placeholder.png" alt="" width="'.$woocommerce->get_image_size('shop_catalog_image_width').'px" height="'.$woocommerce->get_image_size('shop_catalog_image_height').'px" />';
//
//        endwhile;
//
//        echo '</ul><!--/.products-->';
//
//    else :
//
//        _e('No product matching your criteria.');
//
//    endif; // endif $product_count > 0

//    return ob_get_clean();
//
}

add_shortcode("woo_products_by_tags", "woo_products_by_tags_shortcode");


function woo_product_categories_dropdown( $atts )
{

    extract(shortcode_atts(array(
        'count' => '1',
        'hierarchical' => '1',
        'orderby' => ''
    ), $atts));

    ob_start();

    $c = $count;
    $h = $hierarchical;
    $o = (isset($orderby) && $orderby != '') ? $orderby : 'order';

    // Stuck with this until a fix for http://core.trac.wordpress.org/ticket/13258
    woocommerce_product_dropdown_categories($c, $h, 0, $o);

    ?>
    <script type='text/javascript'>
        /* <![CDATA[ */
        var product_cat_dropdown = document.getElementById("dropdown_product_cat");
        function onProductCatChange() {
            if (product_cat_dropdown.options[product_cat_dropdown.selectedIndex].value !== '') {
                location.href = "<?php echo home_url(); ?>/?product_cat=" + product_cat_dropdown.options[product_cat_dropdown.selectedIndex].value;
            }
        }
        product_cat_dropdown.onchange = onProductCatChange;
        /* ]]> */
    </script>
    <?php

    return ob_get_clean();
}
    add_shortcode( 'product_categories_dropdown', 'woo_product_categories_dropdown' );
