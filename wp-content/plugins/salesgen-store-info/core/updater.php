<?php

if ( !class_exists('SalesGen_Updater') ) {
	class SalesGen_Updater {

		public $current_version;
	 
		public $update_path;
	 
		public $plugin_slug;
	 
		public $slug;

		public $domain;

		protected $package = array(
			'ls' => '',
			'exp' => '',
			'package' 	=> 0
		);
	 
		function __construct( $plugin_slug, $current_version = '0.0.1' )
		{
			// Set the class public variables

			$this->domain 		= preg_replace( '/www\./i', '', $_SERVER['SERVER_NAME'] );
			$this->update_path 	= 'https://abc.salesgen.io/modules/';
			
			$this->plugin_slug 	= $plugin_slug;
			$this->current_version 	= $current_version;
			list ( $t1, $t2 ) 	= explode( '/', $plugin_slug );
			$this->slug 		= str_replace( '.php', '', $t2 );

			add_filter( 'site_transient_update_plugins', 				array( &$this, 'check_update' ) );
			add_filter( 'plugins_api', 											array( &$this, 'plugin_info'), 20, 3 );
			add_filter( 'plugins_api_result', 									array( &$this, 'plugins_api_result'), 10, 3 );
			add_action( 'upgrader_process_complete', 									array( &$this, 'after_update'), 10, 2 );
			$this->package = get_option( $this->slug . '_package', $this->package );;
		}

		public function process_ls( $data ) {

			$ls = $data['ls'];
			$act = $data['act'];
					
			$response =  $this->get_version( $act, $ls );

			if( is_object( $response ) ) {
				
				if (
					isset( $response->new_version ) 
					&& version_compare( $this->current_version, $response->new_version, '<' )
				) { 
					//store data to option field
					update_option( $this->slug . '_update', serialize($response) );
				}

				//if it has license key, update epd
				$package = array();
				$package['ls'] = $ls;
				$package['package'] = $response->package_data;
				$package['exp'] = $response->exp;

				if($response->stt == 8) $package['ls'] = '';

				update_option( $this->slug . '_package', $package );
				$package['revoke'] = isset($response->revoke)? $response->revoke : 0;				
				$package['assigned'] = isset($response->assigned)? $response->assigned : 0;				
				$package['activated'] = isset($response->activated)? $response->activated : 0;				
				$package['deactivate'] = isset($response->deactivate)? $response->deactivate : 0;				
				$package['stt'] = $response->stt;
				if( $package['exp'] != '' ) {
					$d = explode( ':', $package['exp']);
					$package['epd'] = $d[0];
				}
				return $package;
			}

			return false;
		}
	 
		public function check_update( $transient ) {

			if ( empty( $transient->checked ) ) {
				return $transient;
			}

			$force_check = isset($_GET['force-check'])? $_GET['force-check'] : 0 ;

			// trying to get from cache first
			if( $force_check || false == $response = get_transient( $this->slug . '_upgrade' ) ) {

				$response = $this->get_version();
				if ( $response !== false ) {
					set_transient( $this->slug . '_upgrade', $response, 43200 ); // 12 hours cache
					if( 
						isset( $this->package['ls'] ) && $this->package['ls'] != '' &&
						$response->exp != $this->package['exp']
					) {
						$this->package['package'] = $response->package_data;
						$this->package['exp'] = $response->exp;
						update_option( $this->slug . '_package', $this->package );
						
					}
					
				}

			}

			// If a newer version is available, add the update
			if( $response ) {
				if (
					isset( $response->new_version ) 
					&& version_compare( $this->current_version, $response->new_version, '<' )
				) { 
					//store data to option field
					$transient->response[ $this->plugin_slug ] = $response;

				}
			}

			return $transient;
		}
	 
		public function plugin_info( $false, $action, $args ) {

			// do nothing if this is not about getting plugin information
			if( 'plugin_information' !== $action ) {
				return false;
			}

			// do nothing if it is not our plugin
			if( $this->slug !== $args->slug ) {
				return false;
			}

			// trying to get from cache first
			if( false == $response = get_transient( $this->slug . '_update' ) ) {

				$response = $this->get_version();
				if ( $response !== false ) {
					set_transient( $this->slug . '_update', $response, 43200 ); // 12 hours cache	
					
				}

			}

			if ( $response ) {
				$update_info = (array) $response;
				$update_info['banners']['low'] = $update_info['banners']['1x'];
				$update_info['banners']['high'] = $update_info['banners']['2x'];
				$update_info['external'] = true;

				return (object) $update_info;
			}

			return false;
		}

		public function get_version( $act = 'check', $ls = '') {
			
			$ls_info = $ls;
			if ( empty( $ls ) ) {
				$d = get_option( $this->slug . '_package', '' );
				$ls_info = isset($d['ls'])? $d['ls'] : '';
			}
			
			$ins_info = get_option( $this->slug . '_ins', '' );
			$insh_info = get_option( $this->slug . '_insh', '' );			

			$data = array(
				'body' => array(
					'action' 	=> 'check',
					'ls' 		=> $ls_info,
					'domain' 	=> preg_replace( '/www\./i', '', $_SERVER['HTTP_HOST'] ),
					'ins'		=> $ins_info,
					'insh'		=> $insh_info,
					'act' 	=> $act,
				),
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json'
				)
			);

			$response = wp_remote_post(
				base64_decode('aHR0cHM6Ly91cGRhdGUuc2FsZXNnZW4uaW8vbW9kdWxlcy8=') . $this->slug . '/',
				$data
			);

			
			
			if ( 
				!is_wp_error( $response ) 
				|| wp_remote_retrieve_response_code( $response ) === 200
				&& ! empty( $response['body'] )
			) {
				return unserialize( $response['body'] );
			}



			return false;
		}
	 

		public function get( $param ) {
			list( $key, $ind ) = explode( '_', $param );
			$prot = $this->$key;
			return isset($prot[ $ind ]) ? $prot[ $ind ] : '';
		}

		public function plugins_api_result( $res, $action, $args ) {
			
			return $res;
		}

		function after_update( $upgrader_object, $options ) {
			if ( $options['action'] == 'update' && $options['type'] === 'plugin' )  {
				// just clean the cache when new plugin version is installed
				delete_transient( $this->slug . '_upgrade' );
			}
		}
	}

}
