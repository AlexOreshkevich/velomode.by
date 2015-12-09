<?php
/**
 * The template for displaying product category thumbnails within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product_cat.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
	$woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );

// Increase loop count
$woocommerce_loop['loop']++;
?>


<li class="product-category product<?php
    if ( ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] == 0 || $woocommerce_loop['columns'] == 1 )
        echo ' first';
	if ( $woocommerce_loop['loop'] % $woocommerce_loop['columns'] == 0 )
		echo ' last';
	?>">


	<?php do_action( 'woocommerce_before_subcategory', $category ); ?>



	<a href="<?php echo get_term_link( $category->slug, 'product_cat' ); ?>">

		<?php
			/**
			 * woocommerce_before_subcategory_title hook
			 *
			 * @hooked woocommerce_subcategory_thumbnail - 10
			 */
			do_action( 'woocommerce_before_subcategory_title', $category );
		?>


		<!---->

		<ul class="wsubcategs">
			<?php
			$wsubargs = array(
				'hierarchical' => 1,
				'show_option_none' => '',
				'hide_empty' => 0,
				'parent' => $category->term_id,
				'taxonomy' => 'product_cat'
			);
			$wsubcats = get_categories($wsubargs);
			foreach ($wsubcats as $wsc):
				?>
				<li>
					<a href="<?php echo get_term_link( $wsc->slug, $wsc->taxonomy );?>"><?php echo $wsc->name;?></a>
					<?php $subcategory_products = new WP_Query( array( 'post_type' => 'product', 'product_cat' => $wsc->slug ) );
					if($subcategory_products->have_posts()):?>
						<ul class="subcat-products">

						</ul>
					<?php endif; wp_reset_query(); // Remember to reset ?>
				</li>
			<?php endforeach;?>
		</ul>

		<!---->

		<h3>
			<?php
				echo $category->name;
			?>
		</h3>

		<script type="text/javascript">
			jQuery(document).ready(function($) {

				$('.wsubcategs').on({ 'touchstart' : function(){
				return
				} });

				var imageWidth = $('.products .product img:first-child').width();
				var imageHeight = $('.products .product img:first-child').height();

				$('.wsubcategs').width(imageWidth);
				$('.wsubcategs').height(imageHeight);
//				Включение-отключение слайдера
//				$(".product-category").addClass("imageSlider");

//                хз что за херь
//				$('ul.products li.product h3').css('margin-top', '200px');
//                хз что за херь
//				if($(window).width() < 1200) {
//					$('ul.products li.product h3').css('margin-top', '190px');
//				} else if($(window).width() < 980) {
//					$('ul.products li.product h3').css('margin-top', '150px');
//				} else if($(window).width() < 767) {
//					$('ul.products li.product h3').css('margin-top', '120px');
//				}

				var ifBelarus = /belarus/;
				var ifChine = /china/;
				var ifRussia = /ru/;


				var menuBy = $('#wcc_widget-4 > div > div > ul > li > ul > li.cat-item.cat-item-2706.mtree-node.mtree-closed > a');
				var menuCh = $('#wcc_widget-4 > div > div > ul > li > ul > li.cat-item.cat-item-2707.mtree-node.mtree-closed > a');
				var menuRu = $('#wcc_widget-4 > div > div > ul > li > ul > li.cat-item.cat-item-2708.mtree-node.mtree-closed > a');

				if(ifBelarus.test(document.location.pathname)) {
					menuBy.click();
				} else if(ifChine.test(document.location.pathname)) {
					menuCh.click();
				} else if(ifRussia.test(document.location.pathname)) {
					menuRu.click();
				}

				$('#products > ul > li h3').css('visibility', 'visible');
			});
		</script>

		<?php
			/**
			 * woocommerce_after_subcategory_title hook
			 */
			do_action( 'woocommerce_after_subcategory_title', $category );
		?>

	</a>


	<?php do_action( 'woocommerce_after_subcategory', $category ); ?>

</li>
