<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

if ( ! function_exists( 'woostify_search' ) ) {
	// Function used to generate search form in sidebar.
	function woostify_search() {
		if ( ! function_exists( 'woostify_options' ) ) {
			return;
		}
		$options = woostify_options( false );
		if ( ! $options['header_search_icon'] ) {
			return;
		}

		$isHide = $options['mobile_menu_hide_search_field'];
		?>
		<div class="site-search <?php echo $isHide ? esc_attr( 'hide' ) : ''; ?>">
			<?php echo do_shortcode( '[fibosearch]' ); ?>
		</div>
		<?php
	}
}

add_action( 'init', function () {
	// When we remove the popups, there is no "click" event on the magnifier icon.
	remove_action( 'woostify_after_footer', 'woostify_dialog_search', 30 );
	remove_action( 'elementor/page_templates/canvas/after_content', 'woostify_dialog_search', 50 );
} );

add_action( 'wp_footer', function () {
	if ( ! function_exists( 'woostify_options' ) ) {
		return;
	}
	$options = woostify_options( false );

	// Desktop search.
	echo '<div id="dgwt-wcas-desktop-search" style="display: none;">' . do_shortcode( '[fibosearch layout="icon"]' ) . '</div>';
	?>
	<script>
		var desktopSearch = document.querySelector('.header-search-icon .icon-search');
		if (desktopSearch !== null) {
			desktopSearch.replaceWith(document.querySelector('#dgwt-wcas-desktop-search > div'));
		}
		document.querySelector('#dgwt-wcas-desktop-search').remove();
	</script>
	<style>
		.site-header .header-search-icon .dgwt-wcas-search-icon {
			width: 24px;
			margin-bottom: 2px;
		}

		.site-header .header-search-icon .dgwt-wcas-ico-magnifier-handler {
			max-width: 24px;
		}
	</style>
	<?php
	// Mobile sticky footer search.
	if ( isset( $options['sticky_footer_bar_enable'] ) && $options['sticky_footer_bar_enable'] ) {
		echo '<div id="dgwt-wcas-mobile-sticky-footer-search" style="display: none;">' . do_shortcode( '[fibosearch layout="icon"]' ) . '</div>';
		?>
		<script>
			var mobileStickyFooterSearch = document.querySelector('.woostify-item-list .header-search-icon');
			if (mobileStickyFooterSearch !== null) {
				mobileStickyFooterSearch.replaceWith(document.querySelector('#dgwt-wcas-mobile-sticky-footer-search > div'));
			}
			document.querySelector('#dgwt-wcas-mobile-sticky-footer-search').remove();
		</script>
		<?php
	}
} );
