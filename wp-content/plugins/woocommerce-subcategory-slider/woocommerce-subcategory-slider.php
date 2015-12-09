<?php
/**
 * Plugin Name: WooCommerce Category Images Modification
 * Plugin URI:
 * Description: Use product image as its category image on category archive pages (To override image for product category, upload one for that category and it will override)
 * Author:
 * Version:
 * Author URI:
 */

class WooCommerce_Category_Images_From_Product {

    private $let_category_image_override = true;
    private $randomize_category_image_from_products = true;

    public function __construct() {
        // Unhooking core's and hooking our custom thumbnail
        add_action( 'plugins_loaded', array( $this, 'overrides' ) );
        add_action( 'woocommerce_before_subcategory_title', array( $this, 'add_product_image_as_woocommerce_subcategory_thumbnail' ) );


    }

    public function overrides() {
        remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
    }

    public function add_product_image_as_woocommerce_subcategory_thumbnail( $category ) {

        if ( $this->let_category_image_override ) {
            if ( get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true ) ) {
                woocommerce_subcategory_thumbnail( $category );
                return;
            }
        }

        $query_args = array(
            'posts_per_page' => $this->randomize_category_image_from_products ? 10 : 1,
            'post_status' => 'publish',
            'post_type' => 'product',
            'meta_query' => array(
                array(
                    'key' => '_visibility',
                    'value' => array( 'catalog', 'visible' ),
                    'compare' => 'IN'
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => $category->term_id
                )
            )
        );

        $products = get_posts( $query_args );

        if ( $products ) {
            echo get_the_post_thumbnail( $products[ array_rand( $products ) ]->ID, 'shop_thumbnail' );
            echo get_the_post_thumbnail( $products[ array_rand( $products ) ]->ID, 'shop_thumbnail' );
            echo get_the_post_thumbnail( $products[ array_rand( $products ) ]->ID, 'shop_thumbnail' );
            echo get_the_post_thumbnail( $products[ array_rand( $products ) ]->ID, 'shop_thumbnail' );
            echo get_the_post_thumbnail( $products[ array_rand( $products ) ]->ID, 'shop_thumbnail' );
        }
    }

}

new WooCommerce_Category_Images_From_Product();