<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPA_Order_Meta {

	/**
	 * Check if price has to be display in cart and checkout
	 * @var type
	 * @var boolean
	 * @access private
	 * @since 3.4.2
	 */
	private $show_price = false;

	public function display_item_meta( $html, $item, $args ) {

		$html = str_replace( '<strong class="wc-item-meta-label">' . WCPA_EMPTY_LABEL . ':</strong>', '', $html );

		return str_replace( WCPA_EMPTY_LABEL . ':', '', $html );
	}

	public function display_meta_value( $display_value, $meta = null, $item = null ) {

		if ( $item != null && $meta !== null ) {
			$wcpa_data = $this->wcpa_meta_by_meta_id( $item, $meta->id );
		} else {
			$wcpa_data = false;
		}
		$out_display_value = $display_value;
		if ( $wcpa_data ) {

			$this->show_price = wcpa_get_option( 'show_price_in_order', true );
			$quantity         = $item->get_quantity();


			if ( $this->show_price == false ) {// dont compare with === , $show_price will be 1 for true and 0 for false
				$product     = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;
				$meta->value = $display_value = $this->order_meta_plain( $wcpa_data, false, $quantity, $product );
			}

			switch ( $wcpa_data['type'] ) {
				case 'text':
				case 'date':
				case 'number':
				case 'time':
				case 'datetime-local':
				case 'header':
					$out_display_value = $display_value;
					break;
				case 'textarea':
					$out_display_value = nl2br( $meta->value );
					break;
				case 'paragraph':
				case 'statictext':
					$out_display_value = do_shortcode( nl2br( $meta->value ) );
					break;
				case 'color':
					$out_display_value = '<span style="color:' . $meta->value . ';font-size: 20px;
            padding: 0;
    line-height: 0;">&#9632;</span>' . $meta->value;
				case 'select':
				case 'checkbox-group':
				case 'radio-group':
					break;
				// return $display_value; //str_replace(', ', '<br>', $meta->value);
				case 'file':
					$out_display_value = $this->display_meta_value_file( $wcpa_data );
					break;
				case 'image-group':

					$out_display_value = $this->display_meta_value_image( $wcpa_data );
					break;
				case 'productGroup':
						$out_display_value = $this->display_meta_value_productGroup( $wcpa_data );
						break;
				case 'color-group':
					$out_display_value = $this->display_meta_value_colorgroup( $wcpa_data );
					break;
				case 'placeselector':

					if ( ! empty( $wcpa_data['value']['formated'] ) ) {
						$display = $wcpa_data['value']['formated'] . '<br>';
						if ( ! empty( $wcpa_data['value']['splited']['street_number'] ) ) {
							$display .= __( 'Street address:', 'wcpa-text-domain' ) . ' ' . $wcpa_data['value']['splited']['street_number'] . ' ' . $wcpa_data['value']['splited']['route'] . ' <br>';
						}
						if ( ! empty( $wcpa_data['value']['splited']['locality'] ) ) {
							$display .= __( 'City:', 'wcpa-text-domain' ) . ' ' . $wcpa_data['value']['splited']['locality'] . '<br>';
						}
						if ( ! empty( $wcpa_data['value']['splited']['administrative_area_level_1'] ) ) {
							$display .= __( 'State:', 'wcpa-text-domain' ) . ' ' . $wcpa_data['value']['splited']['administrative_area_level_1'] . '<br>';
						}
						if ( ! empty( $wcpa_data['value']['splited']['postal_code'] ) ) {
							$display .= __( 'Zip code:', 'wcpa-text-domain' ) . ' ' . $wcpa_data['value']['splited']['postal_code'] . '<br>';
						}
						if ( ! empty( $wcpa_data['value']['splited']['country'] ) ) {
							$display .= __( 'Country:', 'wcpa-text-domain' ) . ' ' . $wcpa_data['value']['splited']['country'] . '<br>';
						}
						if ( isset( $wcpa_data['value']['cords']['lat'] ) && ! empty( $wcpa_data['value']['cords']['lat'] ) ) {
							$display .= __( 'Latitude:', 'wcpa-text-domain' ) . ' ' . $wcpa_data['value']['cords']['lat'] . '<br>';
							$display .= __( 'Longitude:', 'wcpa-text-domain' ) . ' ' . $wcpa_data['value']['cords']['lng'] . '<br>';
							$display .= '<a href="https://www.google.com/maps/?q=' . $wcpa_data['value']['cords']['lat'] . ',' . $wcpa_data['value']['cords']['lng'] . '" target="_blank">' . __( 'View on map', 'wcpa-text-domain' ) . '</a> <br>';
						}
						$out_display_value = $display;
						break;
					} else {
						$out_display_value = $display_value;
						break;
					}


				default:
					$out_display_value = $display_value;
					break;
			}
		} else {
			$out_display_value = $display_value;
		}

		return apply_filters( 'wcpa_order_item_display_meta_value', $out_display_value, $display_value, $wcpa_data );

	}

	private function wcpa_meta_by_meta_id( $item, $meta_id ) {
		$meta_data = $item->get_meta( WCPA_ORDER_META_KEY );

		if ( is_array( $meta_data ) && count( $meta_data ) ) {

			foreach ( $meta_data as $v ) {

				if ( isset( $v['meta_id'] ) && ( $meta_id == $v['meta_id'] ) ) {
					return $v;
				}
			}
		} else {
			return false;
		}

		return false;
	}

	public function order_meta_plain( $v, $show_price = true, $quantity = 1, $product = false ) {


		$field_price_multiplier = 1;
		if ( wcpa_get_option( 'show_field_price_x_quantity', false ) ) {
			$field_price_multiplier = $quantity;
		}

		if (
			( isset( $v['form_data']->form_rules['pric_cal_option_once'] )
			  && $v['form_data']->form_rules['pric_cal_option_once'] === true )
			|| ( isset( $v['form_data']->form_rules['pric_use_as_fee'] ) && $v['form_data']->form_rules['pric_use_as_fee'] === true ) ||
			( isset( $v['is_fee'] ) && $v['is_fee'] === true )
		) {
			$field_price_multiplier = 1;
		}

		if ( $v['type'] == 'file' ) {
			if(isset($v['multiple']) && ($v['multiple'] == true)){
				if(isset($v['value'])) {
					if ( $v['price'] && $show_price ) {
						if ( $product ) {
							$price = wcpa_get_price_cart( $product, $v['price'] );
						} else {
							$price = $v['price'];
						}
						$return = implode( "\r\n", array_map( function ( $a ) use ( $field_price_multiplier, $product ) {
							return $a['file_name'] . ' | ' . $a['url'];
						}, $v['value']));
						return $return.'\r\n( '. wcpa_price( $price * $field_price_multiplier, 1 ) .' )';
					} else {
						$return =  implode( "\r\n", array_map( function ( $a ) {
							return $a['file_name'] . ' | ' . $a['url'];
						}, $v['value'] ) );
						return $return.'\r\n( '. $price .' )';
					}
				}
			} else {
				if ( isset( $v['value']['url'] ) && $v['value']['url'] ) {
					if ( $v['price'] && $show_price ) {
						if ( $product ) {
							$price = wcpa_get_price_cart( $product, $v['price'] );
						} else {
							$price = $v['price'];
						}

						return $v['value']['file_name'] . ' | ' . $v['value']['url'] . ' | (' . wcpa_price( $price * $field_price_multiplier, 1 ) . ')';
					} else {
						return $v['value']['file_name'] . ' | ' . $v['value']['url'];
					}
				} else {
					return '';
				}
			}
		} else if ( $v['type'] == 'image-group' ) {
			if ( $v['price'] && $show_price ) {
				return implode( "\r\n", array_map( function ( $a, $b ) use ( $field_price_multiplier, $product ) {
					if ( $a['i'] === 'other' ) {
						if ( $b ) {
							if ( $product ) {
								$price = wcpa_get_price_cart( $product, $b );
							} else {
								$price = $b;
							}

							return __( $a['label'] . ':', 'wcpa-text-domain' ) . ' ' . $a['value'] . ' | (' . wcpa_price( $price * $field_price_multiplier, 1 ) . ')';
						} else {
							return __( $a['label'] . ':', 'wcpa-text-domain' ) . ' ' . $a['value'];
						}
					} else {
						if ( $b ) {
							if ( $product ) {
								$price = wcpa_get_price_cart( $product, $b );
							} else {
								$price = $b;
							}

							return $a['label'] . ' | ' . $a['image'] . ' | (' . wcpa_price( $price * $field_price_multiplier, 1 ) . ')';
						} else {
							return $a['label'] . ' | ' . $a['image'];
						}
					}
				}, $v['value'], $v['price'] ) );
			} else {
				return implode( "\r\n", array_map( function ( $a ) {
					if ( $a['i'] === 'other' ) {
						return __( $a['label'] . ':', 'wcpa-text-domain' ) . ' ' . $a['value'];
					} else {
						return $a['label'] . ' | ' . $a['image'];
					}
				}, $v['value'] ) );
			}
		} else if ( $v['type'] == 'productGroup' ) {
			if ( $v['price'] && $show_price ) {
				return implode( "\r\n", array_map( function ( $a, $b, $c ) use ( $field_price_multiplier, $product ) {	
					$pro_image = '';
					
					$quantity = (isset($c) && !empty($c)) ? $c : 1;
					if($a){
						
						if ( $a->get_image_id() ) {
							$pro_image = wp_get_attachment_url( $a->get_image_id() );
						} 
					
						if ( $pro_image == '' ) {
							$pro_image = wc_placeholder_img_src( 'woocommerce_thumbnail' );
						}

						if ( $b ) {
							if ( $product ) {
								$price = wcpa_get_price_cart( $product, $b );
							} else {
								$price = $b;
							}

							return $a->get_title() .' | '. 'x '.$quantity . ' | ' . $pro_image . ' | (' . wcpa_price( $price * $field_price_multiplier, 1 ) . ')';
						} else {
							return $a->get_title() .' | '. 'x '.$quantity . ' | ' . $pro_image;
						}
					}
				}, $v['value'], $v['price'], $v['quantities'] ) );
			} else {
				return implode( "\r\n", array_map( function ( $a ) {
					$pro_image = '';
					if($a){
						if ( $a->get_image_id() ) {
							$pro_image = wp_get_attachment_url( $a->get_image_id() );
						} 
					
						if ( $pro_image == '' ) {
							$pro_image = wc_placeholder_img_src( 'woocommerce_thumbnail' );
						}

						return $a->get_title() . ' | ' . $pro_image;
					}
				}, $v['value'] ) );
			}
		} else if ( $v['type'] == 'color-group' ) {
			if ( $v['price'] && $show_price ) {
				return implode( "\r\n", array_map( function ( $a, $b ) use ( $field_price_multiplier, $product ) {
					if ( $a['i'] === 'other' ) {
						if ( $b ) {
							if ( $product ) {
								$price = wcpa_get_price_cart( $product, $b );
							} else {
								$price = $b;
							}

							return __( $a['label'] . ':', 'wcpa-text-domain' ) . ' ' . $a['value'] . ' | (' . wcpa_price( $price * $field_price_multiplier, 1 ) . ')';
						} else {
							return __( $a['label'] . ':', 'wcpa-text-domain' ) . ' ' . $a['value'];
						}
					} else {
						if ( $b ) {
							return $a['label'] . ' | ' . $a['value'] . ' | ' . $a['color'] . ' | (' . wcpa_price( $b * $field_price_multiplier, 1 ) . ')';
						} else {
							return $a['label'] . ' | ' . $a['value'] . ' | ' . $a['color'];
						}
					}
				}, $v['value'], $v['price'] ) );
			} else {
				return implode( "\r\n", array_map( function ( $a ) {
					if ( $a['i'] === 'other' ) {
						return __( $a['label'] . ':', 'wcpa-text-domain' ) . ' ' . $a['value'];
					} else {
						return $a['label'] . ' | ' . $a['value'] . ' | ' . $a['color'];
					}
				}, $v['value'] ) );
			}
		} else if ( $v['type'] == 'placeselector' ) {
			$display = '';

			if ( ! empty( $v['value']['formated'] ) ) {
				$display = $v['value']['formated'] . "\r\n";
				if ( ! empty( $v['value']['splited']['street_number'] ) ) {
					$display .= __( 'Street address:', 'wcpa-text-domain' ) . ' ' . $v['value']['splited']['street_number'] . ' ' . $v['value']['splited']['route'] . "\r\n";
				}
				if ( ! empty( $v['value']['splited']['locality'] ) ) {
					$display .= __( 'City:', 'wcpa-text-domain' ) . ' ' . $v['value']['splited']['locality'] . "\r\n";
				}
				if ( ! empty( $v['value']['splited']['administrative_area_level_1'] ) ) {
					$display .= __( 'State:', 'wcpa-text-domain' ) . ' ' . $v['value']['splited']['administrative_area_level_1'] . "\r\n";
				}
				if ( ! empty( $v['value']['splited']['postal_code'] ) ) {
					$display .= __( 'Zip code:', 'wcpa-text-domain' ) . ' ' . $v['value']['splited']['postal_code'] . "\r\n";
				}
				if ( ! empty( $v['value']['splited']['country'] ) ) {
					$display .= __( 'Country:', 'wcpa-text-domain' ) . ' ' . $v['value']['splited']['country'] . "\r\n";
				}
				if ( isset( $v['value']['cords']['lat'] ) && ! empty( $v['value']['cords']['lat'] ) ) {
					$display .= __( 'Latitude:', 'wcpa-text-domain' ) . ' ' . $v['value']['cords']['lat'] . "\r\n";
					$display .= __( 'Longitude:', 'wcpa-text-domain' ) . ' ' . $v['value']['cords']['lng'] . "\r\n";
				}
			}

			return $display;
		} else if ( is_array( $v['value'] ) ) {
			$is_ver_1_data = true;
			$first         = current( $v['value'] );

			if ( isset( $first['i'] ) ) {
				$is_ver_1_data = false;
			}
			if ( $is_ver_1_data ) {
				if ( $v['price'] && $show_price ) {
					return implode( "\r\n", array_map( function ( $a, $b ) use ( $field_price_multiplier, $product ) {
						if ( $b !== null && $b !== false ) {
							if ( $product ) {
								$price = wcpa_get_price_cart( $product, $b );
							} else {
								$price = $b;
							}

							return $a . ' (' . wcpa_price( $price * $field_price_multiplier, 1 ) . ')';
						} else {
							return $a;
						}
					}, $v['value'], $v['price'] ) );
				} else {
					return implode( "\r\n", $v['value'] );
				}
			} else {
				if ( ( $v['price'] ) && $show_price ) {

					return implode( "\r\n", array_map( function ( $a, $b ) use ( $field_price_multiplier, $product ) {
						if ( ( $a['i'] === 'other' ) ) {

							return __( $a['label'] . ':', 'wcpa-text-domain' ) . ' ' . $a['value'] . ( ( $b !== null && $b !== false ) ? ' (' . wcpa_price( $b * $field_price_multiplier, 1 ) . ')' : '' );
						} else {
							if ( $product ) {
								$price = wcpa_get_price_cart( $product, $b );
							} else {
								$price = $b;
							}

							return $a['label'] . ( ( $b !== null && $b !== false ) ? ' (' . wcpa_price( $price * $field_price_multiplier, 1 ) . ')' : '' );
						}
//                                if ($b) {
//                                    return (($a['i'] === 'other') ? __($a['label'] . ':', 'wcpa-text-domain') . ' ' : '') . $a['label'] . ' (' . wcpa_price($b, 1) . ')';
//                                } else {
//                                    return (($a['i'] === 'other') ? __('Other:', 'wcpa-text-domain') . ' ' : '') . $a['label'];
//                                }
					}, $v['value'], $v['price'] ) );
				} else {
					return trim( array_reduce( $v['value'], function ( $a, $b ) {
						if ( $b['i'] === 'other' ) {
							return $a . "\r\n" . __( $b['label'] . ':', 'wcpa-text-domain' ) . $b['value'];
						} else {
							return $a . "\r\n" . $b['label'];
						}
					} ), "\r\n" );
				}
			}
		} else {
			if ( $v['price'] && $show_price ) {
				if ( $product ) {
					$price = wcpa_get_price_cart( $product, $v['price'] );
				} else {
					$price = $v['price'];
				}

				return $v['value'] . ' (' . wcpa_price( $price * $field_price_multiplier, 1 ) . ')';
			} else {
				return $v['value'];
			}
		}
	}

	public function display_meta_value_file( $v ) {
		$display   = '';
		$hideImage = false;
		if ( is_wc_endpoint_url() && isset( $v['form_data']->hideImageIn_order ) && $v['form_data']->hideImageIn_order ) {
			$hideImage = true;
		}
		if ( ! is_wc_endpoint_url() && isset( $v['form_data']->hideImageIn_email ) && $v['form_data']->hideImageIn_email ) {
			$hideImage = true;
		}


		$value = $v['value'];
		if(isset($v['multiple']) && ($v['multiple']==true)){
			if($value){
				foreach($value as $val){
					if ( isset( $val['url'] ) ) {
						$display .= '<a href="' . $val['url'] . '"  target="_blank" download="' . $val['file_name'] . '">';
						if ( ! $hideImage && in_array( $val['type'], array(
								'image/jpg',
								'image/png',
								'image/gif',
								'image/jpeg'
							) ) ) {
							//  $display .= '<img class="wcpa_img" style="max-width:150px"   src="' . $val['url'] . '" />';
							$display .= '<img class="wcpa_img" width="150" border="0" style="display: block; max-width: 150px;  width: 100%;"   src="' . $val['url'] . '" /><br />';
		
						} else {
							$display .= '<img class="wcpa_icon"  src="' . wp_mime_type_icon( $val['type'] ) . '" />';
						}
						$display .= $val['file_name'] . '</a>';
					}
				}
			}
		} else {
			if ( isset( $value['url'] ) ) {
				$display .= '<a href="' . $value['url'] . '"  target="_blank" download="' . $value['file_name'] . '">';
				if ( ! $hideImage && in_array( $value['type'], array(
						'image/jpg',
						'image/png',
						'image/gif',
						'image/jpeg'
					) ) ) {
					//  $display .= '<img class="wcpa_img" style="max-width:150px"   src="' . $value['url'] . '" />';
					$display .= '<img class="wcpa_img" width="150" border="0" style="display: block; max-width: 150px;  width: 100%;"   src="' . $value['url'] . '" /><br />';

				} else {
					$display .= '<img class="wcpa_icon"  src="' . wp_mime_type_icon( $value['type'] ) . '" />';
				}
				$display .= $value['file_name'] . '</a>';
			}
		}

		return apply_filters( 'wcpa_order_meta_display_file', $display, $value );
	}

	public function display_meta_value_image( $value ) {
		$display = '<div class="wcpa_image_group">';

		$hideImage = false;
		if ( is_wc_endpoint_url() && isset( $value['form_data']->hideImageIn_order ) && $value['form_data']->hideImageIn_order ) {
			$hideImage = true;
		}
		if ( ! is_wc_endpoint_url() && isset( $value['form_data']->hideImageIn_email ) && $value['form_data']->hideImageIn_email ) {
			$hideImage = true;
		}
		foreach ( $value['value'] as $k => $v ) {


			if ( isset( $v['image'] ) && $v['image'] !== false ) {
				$display        .= '<p class="wcpa_image">';
				$img_size_style = ( ( isset( $value['form_data']->disp_size_img ) && $value['form_data']->disp_size_img > 0 ) ? 'style="width:' . $value['form_data']->disp_size_img . 'px"' : '' );
				$img_size_attr  = ( ( isset( $value['form_data']->disp_size_img ) && $value['form_data']->disp_size_img > 0 ) ? 'width="' . $value['form_data']->disp_size_img . '"' : '' );
				if ( ! $hideImage ) {
					$display .= '<img ' . $img_size_style . ' ' . $img_size_attr . ' src="' . $v['image'] . '" style="max-width:100%"  />';
				}
				$display .= $v['label'];
			} else if ( $v['i'] === 'other' ) {
				$display .= '<p class="wcpa_image">';
				$display .= __( 'Other:', 'wcpa-text-domain' ) . ' ' . $v['label'];
			}
			if ( $this->show_price && $value['price'] && is_array( $value['price'] ) ) {
				if ( isset( $value['price'][ $k ] ) && $value['price'][ $k ] !== false ) {
					$display .= '<span class="wcpa_cart_price">(' . wcpa_price( $value['price'][ $k ] ) . ')</span>';
				}
			} else {
				if ( $this->show_price && $value['price'] !== false ) {
					$display .= ' <span class="wcpa_cart_price">(' . wcpa_price( $value['price'] ) . ')</span>';
				}
			}
			$display .= '</p>';
		}
		$display .= '</div>';

		return $display;
	}

	public function display_meta_value_productGroup( $value ) {
		$display = '<div class="wcpa_productGroup">';

		$hideImage = true;
		if(isset($value['form_data']->show_image) && $value['form_data']->show_image){
            if (
				(isset($value['form_data']->hideImageIn_order) && $value['form_data']->hideImageIn_order && is_wc_endpoint_url()) ||
				(isset($value['form_data']->hideImageIn_email) && $value['form_data']->hideImageIn_email && ! is_wc_endpoint_url() )
			) {
                $hideImage = true;
            } else {
                $hideImage = false;
            }
        }
		
		foreach ( $value['value'] as $k => $v ) {
			$pro_image ='';
			if(!$hideImage){
				if ( $v->get_image_id() ) {
					$pro_image = wp_get_attachment_url( $v->get_image_id() );
				} 
			
				if ( ! $pro_image ) {
					$pro_image = wc_placeholder_img_src( 'woocommerce_thumbnail' );
				}
			} 
			$img_size_style = ((isset($value['form_data']->disp_size_img) && $value['form_data']->disp_size_img > 0) ? 'style="width:' . $value['form_data']->disp_size_img . 'px"' : '');

			$display .= '<p class="wcpa_productGroup_item">' . (!($hideImage) ? '<img ' . $img_size_style . ' data-src="' . $pro_image . '"  src="' . $pro_image . '" />' : '');

			$label = $v->get_title();
			if (!empty($label)) {
				$display .= ' <span >' . $label . '</span> ';
			}

			if(!empty($value['quantities'])) {
				$display .= ' <span class="wcpa_productGroup_cart_quantity">x ' . $value['quantities'][$k] . '</span> ';
			}

			if ($value['price'] && is_array($value['price']) && $this->show_price) {
				if (isset($value['price'][$k]) && $value['price'][$k] !== false && $value['price'][$k] != 0) {
					$display .= '<span class="wcpa_cart_price">(' . wcpa_price($value['price'][$k]) . ')</span>';
				}
			} else {
				if ($value['price'] !== false && $this->show_price && $value['price'] != 0) {
					$display .= ' <span class="wcpa_cart_price">(' . wcpa_price($value['price'][$k]) . ')</span>';
				}
			}


			$display .= '</p>';
		}
		$display .= '</div>';

		return $display;
	}

	public function display_meta_value_colorgroup( $value ) {
		$style   = array();
		$display = '<div class="wcpa_color_group " >';
		if ( is_array( $value['value'] ) ) {

			foreach ( $value['value'] as $k => $v ) {
				if ( $k === 'other' ) {
					$display .= '<div class="wcpa_cart_colorgroup">' . __( $v['label'] . ':', 'wcpa-text-domain' ) . ' ' . $v['value'] . '';
				} else {
					$display .= '<div class="wcpa_cart_colorgroup">';

					$disp_size = 30;
					if ( isset( $value['form_data']->disp_size ) && $value['form_data']->disp_size > 10 ) {
						$disp_size = $value['form_data']->disp_size;
					}


					$style['height'] = $disp_size . 'px';
					if ( isset( $value['form_data']->show_label_inside ) && $value['form_data']->show_label_inside ) {
						$style['min-width']   = $disp_size . 'px';
						$style['line-height'] = ( $disp_size - 2 ) . 'px';
					} else {
						$style['width'] = $disp_size . 'px';
					}

					$display .= '<span style="color:' . $v['color'] . ';font-size: 20px;
            padding: 0;
    line-height: 0;">&#9632;</span>' . ( ! wcpa_empty( $v['label'] ) ? $v['label'] : $v['value'] ) . '  ';
				}

				if ( $this->show_price && $value['price'] && is_array( $value['price'] ) ) {
					if ( isset( $value['price'][ $k ] ) && $value['price'][ $k ] !== false ) {
						$display .= '<span class="wcpa_cart_price">(' . wcpa_price( $value['price'][ $k ] ) . ')</span>';
					}
				} else {
					if ( $value['price'] !== false && $this->show_price ) {
						$display .= ' <span class="wcpa_cart_price">(' . wcpa_price( $value['price'] ) . ')</span>';
					}
				}


				$display .= '</div>';
			}
		}
		$display .= '</div>';

		return $display;
	}

	public function email_format_string( $string, $obj ) {

		// $email

		if ( is_string( $string ) && preg_match_all( '/\{(\s)*?wcpa_id_([^}]*)}/', $string, $matches ) ) {
			if ( isset( $obj->id ) ) {
				$order = $obj->object;
				if ( $order && method_exists( $order, 'get_items' ) ) {
					foreach ( $matches[2] as $k => $match ) {
						foreach ( $order->get_items() as $item_id => $item_data ) {
							$meta_data = $item_data->get_meta( WCPA_ORDER_META_KEY );
							foreach ( $meta_data as $v ) {
								if ( $v['form_data']->elementId === $match ) {
									$val    = $this->order_meta_plain( $v, false );
									$string = str_replace( '{wcpa_id_' . $match . '}', $val, $string );
								}
							}
						}

						$string = str_replace( '{wcpa_id_' . $match . '}', '', $string );
					}
				}

			}

		}


		return $string;
	}

	public function checkout_subscription_created( $subscription ) {
		$items    = $subscription->get_items();
		$order_id = $subscription->get_id();
		if ( is_array( $items ) ) {
			foreach ( $items as $item_id => $item ) {
				$this->update_order_item( $item, $order_id );
			}
		}
	}


	public function update_order_item( $item, $order_id ) {
		$meta_data      = $item->get_meta_data();
		$wcpa_meta_data = $item->get_meta( WCPA_ORDER_META_KEY );	
		$qty = $item->get_quantity();

		foreach ( $meta_data as $meta ) {
			$data = (object) $meta->get_data();

			if ( ( $matches = $this->check_wcpa_meta( $data ) ) !== false ) {
				if ( isset( $wcpa_meta_data[ $matches[1] ] ) ) {
					$wcpa_meta_data_item = $wcpa_meta_data[ $matches[1] ];

					if ( $wcpa_meta_data_item['type'] == 'hidden' ||
					     !wcpa_get_option('show_meta_in_order', true) ||
					     ( isset( $wcpa_meta_data_item['form_data']->hideFieldIn_order )
					       && $wcpa_meta_data_item['form_data']->hideFieldIn_order )

					) {
						$item->update_meta_data( '_' . $wcpa_meta_data_item['label'], $data->value, $data->id );
					} else {
						$item->update_meta_data( $wcpa_meta_data_item['label'], $data->value, $data->id );
					}

					$wcpa_meta_data[ $matches[1] ]['meta_id'] = $data->id;

					// Update Product Group Quantity
					if ( $wcpa_meta_data_item['type'] == 'productGroup') {
						if($wcpa_meta_data_item['value']){
							foreach($wcpa_meta_data_item['value'] as $k=>$v) {
								if($v->get_manage_stock()) {
									$stock_quantity = $v->get_stock_quantity();
									$quantity = (isset($wcpa_meta_data_item['quantities']) && isset($wcpa_meta_data_item['quantities'][$k])) 
														? $wcpa_meta_data_item['quantities'][$k] 
														: 1;
									//Quantity depend
									if(
										!(isset($wcpa_meta_data_item['independ_quantity']) && $wcpa_meta_data_item['independ_quantity'])
									) {
										$quantity *= $qty; 
									}
									$new_quantity = $stock_quantity-$quantity;
									$v->set_stock_quantity($new_quantity);
									$v->save();
								}
							}
						}
					}
				}
			}
		}

		$wcpa_meta_data = apply_filters( 'wcpa_order_meta_data', $wcpa_meta_data, $item, $order_id );

		$item->update_meta_data( WCPA_ORDER_META_KEY, $wcpa_meta_data );
		$item->save_meta_data();
	}

	private function check_wcpa_meta( $meta ) {

		preg_match( "/WCPA_id_(.*)/", $meta->key, $matches );
		if ( $matches && count( $matches ) ) {
			return $matches;
		} else {
			return false;
		}
	}

	public function checkout_order_processed( $order_id) {
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		if ( is_array( $items ) ) {
			foreach ( $items as $item_id => $item ) {
				$this->update_order_item( $item, $order_id );
			}
		}
	}

	public function checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {

		if ( empty( $values[ WCPA_CART_ITEM_KEY ] ) ) {
			return;
		}

		$product         = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;
		$conversion_unit = 1;
		if ( $product ) {
			$order_currency = $order->get_currency();
			$base_currency  = get_option( 'woocommerce_currency' );
			if ( $base_currency !== $order_currency ) {
				$mc              = new WCPA_MC();
				$conversion_unit = $mc->get_con_unit( $product, $order_currency, $base_currency );
			}
		}

		$meta_data  = array();
		$i          = 0;
		$quantity   = $item->get_quantity();
		$save_price = wcpa_get_option( 'show_price_in_order_meta', true );
		foreach ( $values[ WCPA_CART_ITEM_KEY ] as $v ) {

			if ( $v['cur_swit']==1 && $conversion_unit !== 1 && isset( $v['price'] ) && $v['price'] !== false ) {
				if ( is_array( $v['price'] ) ) {
					$v['price'] = array_map(
						function ( $price ) use ( $conversion_unit ) {
							return $price * $conversion_unit;
						}, $v['price'] );
				} else {
					$v['price'] = $v['price'] * $conversion_unit;
				}
			}

			$meta_data[ $i ] = $v;
			if ( ! in_array( $v['type'], array( 'separator' ) ) ) {
				if ( $save_price === false ) {

					$item->add_meta_data( 'WCPA_id_' . $i, $this->order_meta_plain( $v, false, $quantity ) );
				} else {

					$item->add_meta_data( 'WCPA_id_' . $i, $this->order_meta_plain( $v, true, $quantity, $product ) );
				}
			}
			$i ++;
		}
		$item->add_meta_data( WCPA_ORDER_META_KEY, $meta_data );
	}

	// admin side */

	public function order_item_line_item_html( $item_id, $item, $product ) {
		$meta_data = $item->get_meta( WCPA_ORDER_META_KEY );

		WCPA_Backend::view( 'order-meta-line-item', [
			'meta_data' => $meta_data,
			'order'     => $item->get_order(),
			'item_id'   => $item_id
		] );
	}

	/**
	 * To hide showing default meat values in backend order data. As it is displaying in other way already
	 */
	public function order_item_get_formatted_meta_data( $formatted_meta, $item ) {

		if ( did_action( 'woocommerce_before_order_itemmeta' ) > 0 ) {
			$meta_data = $item->get_meta( '_wcpa_meta_key_info' );
			foreach ( $formatted_meta as $meta_id => $v ) {
				if ( $this->wcpa_meta_by_meta_id( $item, $meta_id ) ) {
					unset( $formatted_meta[ $meta_id ] );
				}
			}
		}


		return $formatted_meta;
	}

	public function before_save_order_items( $order_id, $items ) {
		$save_price = wcpa_get_option( 'show_price_in_order_meta', true );
		if ( is_array( $items ) && isset( $items['wcpa_meta'] ) ) {
			$wcpa_meta = $items['wcpa_meta'];
			if ( isset( $wcpa_meta['value'] ) && is_array( $wcpa_meta['value'] ) ) {
				foreach ( $wcpa_meta['value'] as $item_id => $data ) {
					if ( ! $item = WC_Order_Factory::get_order_item( absint( $item_id ) ) ) {
						continue;
					}

					//$meta_data = wc_get_order_item_meta($item_id, WCPA_ORDER_META_KEY, true);
					$meta_data = $item->get_meta( WCPA_ORDER_META_KEY );



					//$meta_info = $item->get_meta('_wcpa_meta_key_info');
					//$meta_info = wc_get_order_item_meta('_wcpa_meta_key_info');
					foreach ( $meta_data as $k => $v ) {
						$meta_id       = $meta_data[ $k ]['meta_id'];
						$is_ver_1_data = true;
						if ( is_array( $meta_data[ $k ]['value'] ) ) {
							$first = current( $meta_data[ $k ]['value'] );

							if(!is_object($first)){
								if ( isset( $first['i'] ) ) {
									$is_ver_1_data = false;
								}
							}
						}

						if ( isset( $data[ $k ] ) ) {
							$meta_value_temp = array( 'type' => false, 'value' => false, 'price' => false );
							//sanitization has to do
							if ( $v['type'] == 'file' ) {
								if(isset($v['multiple'])) {
									if($meta_data[ $k ]['value']){
										foreach($meta_data[ $k ]['value'] as $key => $f_val){
											if($f_val){
												if($f_val['url'] !== trim( $data[ $k ][$index] )){
													$f_val['url']       = trim( sanitize_text_field( $data[ $k ][ $index ] ) );
													$f_val['path']      = false;
													$f_val['file_name'] = wp_basename( $data[ $k ][ $index ] );
													$file_type                             = wp_check_filetype( $data[ $k ][ $index ] );
													$meta_value_temp['value'][ $index ]    = $f_val;
													$meta_value_temp['type'][ $index ]     = $v['type'];
													$f_val['type']      = $file_type['type'];
												}
												$meta_data[ $k ]['value'][$key] = $f_val;
												$index++;
											} else {
												unset( $meta_data[ $k ]['value'][$key] );
											}
										}
										// To Filter Empty URL
										function wcpa_filter_empty_url($filter) {
											return isset($filter['url']) && !empty($filter['url']);
										}
										$meta_data[ $k ]['value'] = array_filter($meta_data[ $k ]['value'], 'wcpa_filter_empty_url');
									
										$price = ( isset( $wcpa_meta['price'][ $item_id ][ $k ] ) && $wcpa_meta['price'][ $item_id ][ $k ] ) ?
											$wcpa_meta['price'][ $item_id ][ $k ] :
										false;
									} else {
										unset( $meta_data[ $k ]['value'] );
									}
								} else {
									if ( $meta_data[ $k ]['value']['url'] !== trim( $data[ $k ] ) ) { // check if has made any changes to file value/url
										$meta_data[ $k ]['value']['url']       = trim( sanitize_text_field( $data[ $k ] ) );
										$meta_data[ $k ]['value']['path']      = false;
										$meta_data[ $k ]['value']['file_name'] = wp_basename( $data[ $k ] );
										$file_type                             = wp_check_filetype( $data[ $k ] );
										$meta_value_temp['value']              = $meta_data[ $k ]['value'];
										$meta_value_temp['type']               = $v['type'];
										$meta_data[ $k ]['value']['type']      = $file_type['type'];
									}

									$price = ( isset( $wcpa_meta['price'][ $item_id ][ $k ] ) && $wcpa_meta['price'][ $item_id ][ $k ] ) ?
										$wcpa_meta['price'][ $item_id ][ $k ] :
										false;
								}

							} else if( $v['type'] == 'productGroup'){
								$price = array();
								foreach ( $meta_data[ $k ]['value'] as $m => $val ) {
									if(isset($data[ $k ][ $m ]['value'])) {
										if($meta_data[ $k ]['value'][ $m ]->get_id() != intval($data[ $k ][ $m ]['value'])){
											$meta_data[ $k ]['value'][ $m ] = wc_get_product( intval($data[ $k ][ $m ]['value'], 10) );
										}
									} else {
										unset( $meta_data[ $k ]['value'][ $m ] );
									}

									if(isset($data[ $k ][ $m ]['quantity'])) {
										if($meta_data[ $k ]['quantities'][ $m ] != intval($data[ $k ][ $m ]['quantity'])){
											$meta_data[ $k ]['quantities'][ $m ] = intval($data[ $k ][ $m ]['quantity']);
										}
									} else {
										unset( $meta_data[ $k ]['quantities'][ $m ] );
									}

									$price[ $m ] = ( isset( $wcpa_meta['price'][ $item_id ][ $k ][ $m ] ) && $wcpa_meta['price'][ $item_id ][ $k ][ $m ] ) ?
												$wcpa_meta['price'][ $item_id ][ $k ][ $m ] :
												false;
								}
							} else if ( $v['type'] == 'placeselector' ) {

								$meta_data[ $k ]['value']['formated'] = $this->sanitize_values( $data[ $k ]['formated'], $v['type'] );
								$splited                              = [
									'street_number',
									'route',
									'locality',
									'administrative_area_level_1',
									'postal_code',
									'country'
								];
								foreach ( $splited as $fl_name ) {
									if ( isset( $data[ $k ][ $fl_name ] ) ) {
										$meta_data[ $k ]['value']['splited'][ $fl_name ] = $this->sanitize_values( $data[ $k ][ $fl_name ], $v['type'] );
									}
								}
								if ( isset( $data[ $k ]['lat'] ) ) {
									$meta_data[ $k ]['value']['cords']['lat'] = $this->sanitize_values( $data[ $k ]['lat'], $v['type'] );
								}
								if ( isset( $data[ $k ]['lng'] ) ) {
									$meta_data[ $k ]['value']['cords']['lng'] = $this->sanitize_values( $data[ $k ]['lng'], $v['type'] );
								}
								$price   = array();
								$price[] = ( isset( $wcpa_meta['price'][ $item_id ][ $k ] ) && $wcpa_meta['price'][ $item_id ][ $k ] ) ?
									$wcpa_meta['price'][ $item_id ][ $k ] :
									false;
							} else if ( is_array( $data[ $k ] ) ) {
								$meta_value_temp['value'] = array();
								$price                    = array();
								if ( $v['type'] == 'image-group' ) {
									foreach ( $meta_data[ $k ]['value'] as $m => $val ) {
										if ( isset( $data[ $k ][ $m ] ) ) {

											$meta_data[ $k ]['value'][ $m ]['label'] = $this->sanitize_values( $data[ $k ][ $m ]['label'], $v['type'] );

											$meta_data[ $k ]['value'][ $m ]['value'] = $this->sanitize_values( $data[ $k ][ $m ]['value'], $v['type'] );
											$file_type                               = wp_check_filetype( $meta_data[ $k ]['value'][ $m ]['value'] );

											if ( in_array( $file_type['type'], array(
												'image/jpg',
												'image/png',
												'image/gif',
												'image/jpeg'
											) ) ) {
												$meta_data[ $k ]['value'][ $m ]['image'] = $meta_data[ $k ]['value'][ $m ]['value']; // $this->sanitize_values($data[$k][$m]['value'], $v['type']);
											} else {
												$meta_data[ $k ]['value'][ $m ]['image'] = false;
											}
											$price[ $m ] = ( isset( $wcpa_meta['price'][ $item_id ][ $k ][ $m ] ) && $wcpa_meta['price'][ $item_id ][ $k ][ $m ] ) ?
												$wcpa_meta['price'][ $item_id ][ $k ][ $m ] :
												false;
										} else {
											unset( $meta_data[ $k ]['value'][ $m ] );
										}
									}
								} else if ( $is_ver_1_data ) {

									$meta_data[ $k ]['value'] = $this->sanitize_values( $data[ $k ], $v['type'] );
									$meta_value_temp['value'] = $meta_data[ $k ]['value'];
									$meta_value_temp['type']  = $v['type'];
									$meta_value               = $this->order_meta_plain( $meta_value_temp, $save_price );
									$item->update_meta_data( $v['label'], $meta_value, $meta_id );
									$price = ( isset( $wcpa_meta['price'][ $item_id ][ $k ] ) && $wcpa_meta['price'][ $item_id ][ $k ] ) ?
										$wcpa_meta['price'][ $item_id ][ $k ] :
										false;
								} else {
									foreach ( $meta_data[ $k ]['value'] as $m => $val ) {
										if ( isset( $data[ $k ][ $m ] ) ) {
											$meta_data[ $k ]['value'][ $m ]['label'] = $this->sanitize_values( $data[ $k ][ $m ]['label'], $v['type'] );
											$meta_data[ $k ]['value'][ $m ]['value'] = $this->sanitize_values( $data[ $k ][ $m ]['value'], $v['type'] );
											$price[ $m ]                             = ( isset( $wcpa_meta['price'][ $item_id ][ $k ][ $m ] ) && $wcpa_meta['price'][ $item_id ][ $k ][ $m ] ) ?
												$wcpa_meta['price'][ $item_id ][ $k ][ $m ] :
												false;
										} else {
											unset( $meta_data[ $k ]['value'][ $m ] );
										}
									}
								}
							} else {
								if ( $v['type'] !== 'paragraph' && $v['type'] !== 'header' ) {
									$meta_data[ $k ]['value'] = $this->sanitize_values( $data[ $k ], $v['type'] );
								}

								$price = ( isset( $wcpa_meta['price'][ $item_id ][ $k ] ) && $wcpa_meta['price'][ $item_id ][ $k ] ) ?
									$wcpa_meta['price'][ $item_id ][ $k ] :
									false;
							}

							$meta_value_temp['value'] = $meta_data[ $k ]['value'];
							if(isset($meta_data[ $k ]['quantities'])) {
								$meta_value_temp['quantities'] = $meta_data[ $k ]['quantities'];
							}
							$meta_value_temp['type']  = $v['type'];
							$meta_data[ $k ]['price'] = $price;
							$meta_value_temp['price'] = $price;

							$meta_value = $this->order_meta_plain( $meta_value_temp, $save_price );

							if ( $v['type'] == 'hidden' ||
							     !wcpa_get_option('show_meta_in_order', true) ||
							     ( isset( $v['form_data']->hideFieldIn_order ) && $v['form_data']->hideFieldIn_order ) ) {
								$item->update_meta_data( '_' . $v['label'], $meta_value, $meta_id );
							} else {
								$item->update_meta_data( $v['label'], $meta_value, $meta_id );
							}

						} else {
							$item->delete_meta_data_by_mid( $meta_id );
							unset( $meta_data[ $k ] );
						}
					}
					$item->update_meta_data( WCPA_ORDER_META_KEY, $meta_data );
					$item->save();
				}
			}
		}
	}

	public function sanitize_values( $value, $type ) {
		if ( is_array( $value ) ) {
			array_walk( $value, function ( &$a, $b ) {
				sanitize_text_field( $a );
			} ); // using this array_wal method to preserve the keys

			return stripslashes($value);
		} else if ( $type == 'textarea' ) {
			return stripslashes(sanitize_textarea_field( $value ));
		} else {
			return stripslashes(sanitize_text_field( $value ));
		}
	}

}
