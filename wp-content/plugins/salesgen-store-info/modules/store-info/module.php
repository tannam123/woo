<?php
/**
 * Module Name: Store Info
 * Author: SalesGen
 *
 * @package SalesGen
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SG_StoreInfo' ) ) {
	class SG_StoreInfo{

		private static $instance = null;

		private $options = array();

		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self();
			}

			return self::$instance;
		}
		
		public function __construct() {
			$this->loader     = SALESGEN_STOREINFO_Loader::get_instance();
			$this->url        = SALESGEN_SI_URL . 'modules/store-info/';
			
			if ( !is_admin() ) {
				
				add_filter('theme_mod_footer_copyright_content', array( $this, 'theme_mod_content'), 999);
				add_filter('theme_mod_custom_html_content', array( $this, 'theme_mod_content'), 999);
				add_shortcode( 'store_domain', array( $this, 'store_domain') );
				add_shortcode( 'store_name', array( $this, 'store_name') );
				add_shortcode( 'store_email', array( $this, 'store_email') );
				add_shortcode( 'store_phone', array( $this, 'store_phone') );
				add_shortcode( 'store_address', array( $this, 'store_address') );
				add_shortcode( 'store_address2', array( $this, 'store_address2') );
				add_shortcode( 'store_categories', array( $this, 'sn_store_categories') );
				
				//flatsome contact
				add_filter('theme_mod_contact_phone', array( $this, 'theme_mod_content'), 999);
				add_filter('theme_mod_contact_email', array( $this, 'theme_mod_content'), 999);
				add_filter('theme_mod_contact_email_label', array( $this, 'theme_mod_content'), 999);
				add_filter('theme_mod_contact_location', array( $this, 'theme_mod_content'), 999);
				add_filter('theme_mod_contact_location_label', array( $this, 'theme_mod_content'), 999);
				add_filter('theme_mod_contact_hours_details', array( $this, 'theme_mod_content'), 999);

			} else{
				add_action( 'admin_menu', array( $this, 'admin_menu' ), 999 );
				add_action( 'admin_init', array( $this, 'register_settings') );
				add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ), 10 );
				
			}

			add_action('added_option', array( &$this, 'option_added'), 10, 2);
			add_action('updated_option', array( &$this, 'option_updated'), 10, 3);

			$storeinfo_woocommerce_recipients = get_option('storeinfo_woocommerce_recipients');

			if ( !empty($storeinfo_woocommerce_recipients) ) {
				//apply filter for woocommerce settings
				$recipients = array('woocommerce_new_order_settings', 'woocommerce_failed_order_settings', 'woocommerce_cancelled_order_settings');
				foreach ($recipients as $option) {
					add_filter("option_{$option}", array($this, 'recipients'), 99, 2);
				}
			}

			add_filter( 'woocommerce_order_number', array( &$this, 'woocommerce_order_number'), 99 );
			add_filter( 'woocommerce_shortcode_order_tracking_order_id', array( &$this, 'order_tracking_order_id'), 99 );
		}



		function theme_mod_content( $text ) {
			if ( is_string($text)) 
				do_shortcode($text);
			else 
				return $text;
		}

		public function option_added( $option, $value  ) {

			if ( $option == 'storeinfo_admin_email_apply' && $value == 1  ) {
				$store_email = get_option('storeinfo_store_email');
				if ( !empty($store_email) ) update_option('admin_email', $store_email);
			}
		}

		public function option_updated( $option, $old_value, $value  ) {
			if ( strpos($option, "storeinfo") !== false )
				error_log("{$option}:{$value}");

			if ( $option == 'storeinfo_admin_email_apply' && $value == 1  ) {

				$store_email = get_option('storeinfo_store_email');

				if ( !empty($store_email) ) update_option('admin_email', $store_email);
			}

			if ( $option == 'storeinfo_store_email' && !empty($value) ) {
				$email_apply = get_option('storeinfo_admin_email_apply');
				if ( $email_apply ) {
					update_option('admin_email', $value);		
				}
			}

		}



		function woocommerce_order_number( $order_id ) {
			$prefix = get_option( 'storeinfo_woocommerce_order_prefix' );
			$new_order_id = $order_id;
			if ( !empty($prefix) ) {
				$new_order_id = $prefix . $order_id;	
			}
			
			return $new_order_id;
		}

		function order_tracking_order_id( $order_id ) {
			$prefix = get_option( 'storeinfo_woocommerce_order_prefix' );
			if ( !empty($prefix) ) {
				return ltrim($order_id, $prefix);
			}
			return $order_id;
		}

		public function recipients( $value, $option ) {

			if ( is_array($value) ) {
				$storeinfo_woocommerce_recipients = get_option('storeinfo_woocommerce_recipients');
				$value['recipient'] = $storeinfo_woocommerce_recipients;
			}
			return $value;
		}

		public function admin_menu() {

			add_options_page(
				'Store Info',
				'Store Info',
				'manage_options',
				'storeinfo',
				array($this, 'admin_page')
			);
		}

		public function admin_page() {
			?>

			<div class="wrap">
				<h1>Store Info</h1>
				<div class="salesgen-setting-box">
					<div class="salesgen-body-form">
						
						<p>Plugin này sẽ giúp bạn thay đổi thông tin trên toàn bộ store một cách nhanh chóng mà không cần phải vào từng page để chỉnh sửa.</p>
						<p>Vui lòng xem hướng dẫn sử dụng ở bên dưới.</p>
						<form method="post" action="options.php">
							<?php settings_fields( 'storeinfo' ); ?>
							<?php do_settings_sections( 'storeinfo' ); ?>
							<?php submit_button(); ?>
						</form>
						<h2>Hướng dẫn sử dụng</h2>
						<p>Các thông tin trong mục Store Information sẽ được áp dụng cho các shortcode tương ứng. Bạn chỉ cần sử dụng các shortcode này bên trong nội dung các page hoặc text widgets.</p>
						<p>Nội dung của bạn trước khi sử dụng shortcode sẽ có dạng như ảnh dưới. Bạn chỉ cần thay thế các nội dung đc tô vàng thành các shortcode tưng ứng</p>
						<img src="<?php echo $this->url?>images/store-info-before.png" style="max-width: 600px;"/>
						<p>Nội dung sử dụng shortcode:</p>
						<img src="<?php echo $this->url?>images/store-info-after.png" style="max-width: 600px;"/>
					</div>
					<div class="salesgen-sidebar">
						<div class="salesgen-sidebar-box">
							<h2>SalesGen Products</h2>
							<p>
								Breaking your revenue with our products.
							</p>
							<ul>
								<li>Upsell & Cross Sell</li>
								<li>Product Visibility</li>
								<li>Hide Your Product with Paypal</li>
								<li>Payments Spinner</li>
								<li>Big Data Store Supporter</li>
								<li>And more…</li>
							</ul>
							<a class="salesgen-button-sidebar" href="https://www.salesgen.io/products/?utm_source=clientsite&utm_medium=storeinfo&utm_campaign=sidebar" target="_blank">Check our products</a>
						</div>
						
					</div>
				</div>
			</div>
			<?php
		}

		public function register_settings() {

			$option_group = 'storeinfo';
			$option_section = 'storeinfo';

			register_setting( $option_group, 'storeinfo_store_address' );
			register_setting( $option_group, 'storeinfo_store_address2' );
			register_setting( $option_group, 'storeinfo_store_email' );
			register_setting( $option_group, 'storeinfo_store_name' );
			register_setting( $option_group, 'storeinfo_store_phone' );
			register_setting( $option_group, 'storeinfo_woocommerce_recipients' );
			register_setting( $option_group, 'storeinfo_woocommerce_order_prefix' );
			register_setting( $option_group, 'storeinfo_admin_email_apply' );

			add_settings_section(
	            'storeinfo', // ID
	            'Store Infomation', // Title
	            false, // Callback
	            'storeinfo' // Page
	        );  

			add_settings_field( 
				'storeinfo_store_name', 
				'Store Name', 
				array( $this, 'store_name_text'), 
				$option_group,
				$option_section
			);
			add_settings_field( 
				'storeinfo_store_email', 
				'Store Email', 
				array( $this, 'store_email_text'),
				$option_group,
				$option_section
			);
			add_settings_field( 
				'storeinfo_store_address', 
				'Store Address', 
				array( $this, 'store_address_text'), 
				$option_group,
				$option_section
			);
			add_settings_field( 
				'storeinfo_store_address2', 
				'Store Address 2', 
				array( $this, 'store_address2_text'), 
				$option_group,
				$option_section
			);
			add_settings_field( 
				'storeinfo_store_phone', 
				'Store Phone', 
				array( $this, 'store_phone_text'), 
				$option_group,
				$option_section
			);
			

			$option_section = 'tweaks';
			add_settings_section(
	            'tweaks', // ID
	            'Extras', // Title
	            false, // Callback
	            'storeinfo' // Page
	        );  
			
			add_settings_field(  
			    'storeinfo_woocommerce_order_prefix',  
			    'Woocommerce Order Prefix',  
			    array( $this, 'woocommerce_order_prefix_text'), 
			    $option_group, 
			    $option_section 
			);			
			add_settings_field(  
			    'storeinfo_woocommerce_recipients',  
			    'Woocommerce Recipients',  
			    array( $this, 'woocommerce_recipients_text'), 
			    $option_group, 
			    $option_section 
			);

			add_settings_field(  
			    'storeinfo_admin_email_apply',  
			    'Apply Email for Admin Email',  
			    array( $this, 'admin_email_apply'), 
			    $option_group, 
			    $option_section 
			);
			
		}

		function admin_email_apply() {

			$options = get_option( 'storeinfo_admin_email_apply' );

			$html = '<input type="checkbox" id="storeinfo_admin_email_apply" name="storeinfo_admin_email_apply" value="1"' . checked( 1, $options, false ) . '/>';
			$html .= '<p class="description">Sử dụng store email cho admin email mà không cần gửi email xác nhận.</p>';

			echo $html;

		}

		function woocommerce_order_prefix_text() 
		{
			$store_address = get_option( 'storeinfo_woocommerce_order_prefix' );
			echo '<input name="storeinfo_woocommerce_order_prefix" type="text" id="storeinfo_woocommerce_order_prefix" value="'.$store_address.'" class="regular-text">';
			echo '<p class="description">Thêm tiền tố trước order ID. Giúp bạn phân biệt đơn hàng từ store nào khi sử dụng chung 1 account payment cho nhiều store.</p>';
		}

		function woocommerce_recipients_text() 
		{
			$store_address = get_option( 'storeinfo_woocommerce_recipients' );
			echo '<input name="storeinfo_woocommerce_recipients" type="text" id="storeinfo_woocommerce_recipients" value="'.$store_address.'" class="regular-text">';
			echo '<p class="description">Thay thế các email nhận thông tin orders của Woocommerce. Áp dụng cho các emails: New order, Cancelled order, Failed order </p>';
		}

		function store_address2_text() 
		{
			$store_address = get_option( 'storeinfo_store_address2' );
			echo '<input name="storeinfo_store_address2" type="text" id="storeinfo_store_address2" value="'.$store_address.'" class="regular-text">';
			echo '<p class="description">Địa chỉ thứ 2 của store. Shortcode: [store_address2]</p>';
		}


		function store_address_text() 
		{
			$store_address = get_option( 'storeinfo_store_address' );
			echo '<input name="storeinfo_store_address" type="text" id="storeinfo_store_address" value="'.$store_address.'" class="regular-text">';
			echo '<p class="description">Địa chỉ thứ 1 của store. Shortcode: [store_address]</p>';
		}
		function store_name_text() 
		{
			$store_name = get_option( 'storeinfo_store_name' );
			echo '<input name="storeinfo_store_name" type="text" id="storeinfo_store_name" value="'.$store_name.'" class="regular-text">';
			echo '<p class="description">Tên store hiển thị trong nội dung của các đoạn text, cấu hình này không ảnh hưởng đến tiêu đề của store trên thanh tiêu đề của trình duyệt. Shortcode: [store_name]</p>';
		}
		function store_email_text() 
		{
			$store_email = get_option( 'storeinfo_store_email' );
			echo '<input name="storeinfo_store_email" type="text" id="storeinfo_store_email" value="'.$store_email.'" class="regular-text">';
			echo '<p class="description">Email store hiển thị trong nội dung của các đoạn text. Shortcode: [store_email]</p>';
		}

		function store_phone_text() 
		{
			$val = get_option( 'storeinfo_store_phone' );
			echo '<input name="storeinfo_store_phone" type="text" id="storeinfo_store_phone" value="'.$val.'" class="regular-text">';
			echo '<p class="description">Số điện thoại store hiển thị trong nội dung của các đoạn text. Shortcode: [store_phone]</p>';
		}


		function store_name() {
			return get_option('storeinfo_store_name');
		}
		function store_email() {
			return get_option('storeinfo_store_email');
		}
		function store_phone() {
			return get_option('storeinfo_store_phone');
		}
		function store_address() {
			return get_option('storeinfo_store_address');
		}
		function store_address2() {
			return get_option('storeinfo_store_address2');
		}
		function sn_store_categories() {
			return wp_list_categories( array('taxonomy' => 'product_cat', 'echo'  => 0, 'title_li' => '') );
		}
		function store_domain() {
			$url = home_url();
			$parse = parse_url($url);
			return $parse['host'];
		}

		public function admin_scripts( $hook )	{
			$screen = get_current_screen();
			if ( in_array( $screen->id, array( 'settings_page_storeinfo' ) ) )
			{
				wp_register_style( 'salesgen_store_info_admin_css', $this->url . 'assets/admin.css', false, SGSI );
	    		wp_enqueue_style( 'salesgen_store_info_admin_css' );
			}
		}

	}


	SG_StoreInfo::get_instance();


}