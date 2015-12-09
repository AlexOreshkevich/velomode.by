<h3><a href="<?php echo esc_url( add_query_arg( 'tab', 'nuke' ) ); ?>"><?php _e( 'Nuke', 'woo_st' ); ?></a></h3>
<p><?php _e( 'Permanently remove WooCommerce generated details from your WordPress database.', 'woo_st' ); ?></p>
<ul class="ul-disc">
	<li><a href="<?php echo esc_url( add_query_arg( 'tab', 'nuke' ) ); ?>#empty-woocommerce-tables"><?php _e( 'Empty WooCommerce Tables', 'woo_st' ); ?></a></li>
	<li><a href="<?php echo esc_url( add_query_arg( 'tab', 'nuke' ) ); ?>#empty-product-by-category"><?php _e( 'Empty Products by Product Category', 'woo_st' ); ?></a></li>
	<li><a href="<?php echo esc_url( add_query_arg( 'tab', 'nuke' ) ); ?>#empty-wordpress-tables"><?php _e( 'Empty WordPress Tables', 'woo_st' ); ?></a></li>
</ul>

<h3><a href="<?php echo esc_url( add_query_arg( 'tab', 'tools' ) ); ?>"><?php _e( 'Tools', 'woo_st' ); ?></a></h3>
<p><?php _e( 'Other WooCommerce actions for store and database maintenance.', 'woo_st' ); ?></p>
<ul class="ul-disc">
	<li><a href="<?php echo esc_url( add_query_arg( 'tab', 'tools' ) ); ?>"><?php _e( 'Re-link rogue Products to Simple Product Type', 'woo_st' ); ?></a></li>
</ul>