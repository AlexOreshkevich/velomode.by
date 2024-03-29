<?php

/**

*

* This program is a free software; you can use it and/or modify it under the terms of the GNU

* General Public License as published by the Free Software Foundation; either version 2 of the License,

* or (at your option) any later version.

*

* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without

* even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

*

* You should have received a copy of the GNU General Public License along with this program; if not, write

* to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

*

* @package   	Customizr

* @subpackage 	functions

* @since     	1.0

* @author    	Nicolas GUILLAUME <nicolas@presscustomizr.com>

* @copyright 	Copyright (c) 2013-2015, Nicolas GUILLAUME

* @link      	http://presscustomizr.com/customizr

* @license   	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

*/


/**

* This is where Customizr starts. This file defines and loads the theme's components :

* 1) A function tc__f() used everywhere in the theme, extension of WP built-in apply_filters()

* 2) Constants : CUSTOMIZR_VER, TC_BASE, TC_BASE_CHILD, TC_BASE_URL, TC_BASE_URL_CHILD, THEMENAME, TC_WEBSITE

* 3) Default filtered values : images sizes, skins, featured pages, social networks, widgets, post list layout

* 4) Text Domain

* 5) Theme supports : editor style, automatic-feed-links, post formats, navigation menu, post-thumbnails, retina support

* 6) Plugins compatibility : jetpack, bbpress, qtranslate, woocommerce and more to come

* 7) Default filtered options for the customizer

* 8) Customizr theme's hooks API : front end components are rendered with action and filter hooks

*

* The method TC__::tc__() loads the php files and instanciates all theme's classes.

* All classes files (except the class__.php file which loads the other) are named with the following convention : class-[group]-[class_name].php

*

* The theme is entirely built on an extensible filter and action hooks API, which makes customizations easy as breeze, without ever needing to modify the core structure.

* Customizr's code acts like a collection of plugins that can be enabled, disabled or extended. More here : http://presscustomizr.com/customizr/hooks-api

*

*/


//Fire Customizr

require_once( get_template_directory() . '/inc/init.php' );


/**

* The best and safest way to extend Customizr with your own custom functions is to create a child theme.

* You can add functions here but they will be lost on upgrade. If you use a child theme, you are safe!

* More informations on how to create a child theme with Customizr here : http://presscustomizr.com/customizr/#child-theme

*/

add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 20;' ), 20 );
/**
 * Заменяем стандартную пагинацию WooCommerce на постраничную  навигацию WP-PageNavi
 *
 */

remove_action('woocommerce_pagination', 'woocommerce_pagination', 10);

function woocommerce_pagination() {

        wp_pagenavi();     
    }
add_action( 'woocommerce_pagination', 'woocommerce_pagination', 10);



function isa_woocommerce_all_pa(){
 
    global $product;
    $attributes = $product->get_attributes();
 
    if ( ! $attributes ) {
        return;
    }
 
    $out = '<ul class="custom-attributes">';
 
    foreach ( $attributes as $attribute ) {
 
 
        // skip variations
        if ( $attribute['is_variation'] ) {
        continue;
        }
 
 
        if ( $attribute['is_taxonomy'] ) {
 
            $terms = wp_get_post_terms( $product->id, $attribute['name'], 'all' );
 
            // get the taxonomy
            $tax = $terms[0]->taxonomy;
 
            // get the tax object
            $tax_object = get_taxonomy($tax);
 
            // get tax label
            if ( isset ($tax_object->labels->name) ) {
                $tax_label = $tax_object->labels->name;
            } elseif ( isset( $tax_object->label ) ) {
                $tax_label = $tax_object->label;
            }
 
            foreach ( $terms as $term ) {
 
                $out .= '<li class="' . esc_attr( $attribute['name'] ) . ' ' . esc_attr( $term->slug ) . '">';
                $out .= '<span class="attribute-label">' . $tax_label . ': </span> ';
                $out .= '<span class="attribute-value">' . $term->name . '</span></li></br>';
 
            }
 
        } else {
 
            $out .= '<li class="' . sanitize_title($attribute['name']) . ' ' . sanitize_title($attribute['value']) . '">';
            $out .= '<span class="attribute-label">' . $attribute['name'] . ': </span> ';
            $out .= '<span class="attribute-value">' . $attribute['value'] . '</span></li></br>';
        }
    }
 
    $out .= '</ul>';
 
    echo $out;
}
add_action('woocommerce_single_product_summary', 'isa_woocommerce_all_pa', 25);