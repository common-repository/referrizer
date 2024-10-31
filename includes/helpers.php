<?php

if ( ! function_exists( 'is_referrizer_api_token_valid' ) ) {
	/**
	 * @param $value
	 *
	 * @return bool
	 */
	function is_referrizer_api_token_valid( $value ) {
		$key = $value;
		if ( is_array( $value ) ) {
			$key = isset( $value['api_key'] ) ? $value['api_key'] : null;
		}

		return strlen( trim( $key ) ) === 32;
	}
}
