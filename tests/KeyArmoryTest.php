<?php
/**
 * This file is part of the KeyArmory API Software Development Kit for PHP 5.5+.
 *
 * License: ISC
 *
 * The license can be found at the root directory of this package.
 */

namespace KeyArmory;


class KeyArmoryTest extends \PHPUnit_Framework_TestCase {
	var $key_id = '';

	function test_compose_url() {
		$key_id = '101';
		$sample_token = 'base64_encrypted_text';


		$keyarmory = new KeyArmory($this->key_id);

		$url =$keyarmory->composeUrl(
			'encryption/token',
			[
				'key_id' => $key_id,
				'token'  => $sample_token,
			]
		);

		print $url;
		$this->assertEquals(
			"https://api.keyarmory.com/v1/encryption/token?key_id=$key_id&token=$sample_token",
			$url,
			'The composed URL does not match expected URL structure'
		);
	}

	function test_encryption() {
		$keyarmory = new KeyArmory( $this->key_id );

		$encrypted_token = $keyarmory->encrypt('This is a test message');

		//echo $encrypted_token;
	}

	function test_decryption() {
		$data = 'This is a test message';

		$encrypted_data = 'ka:16:GXLkK+KznA6bDJX/OU2Tse9ytG/VyJRk2tpk3ucxBfob3nKJ9TLU2y7gA/OcSzOj:MeyoHh79bYhrm/6HF4jozlgpYRSW9wiCuH3SVazDBBOviHAK6Ya/5GAnUGv+o4PQ5TNreQxHSLQB5GbYtv3lWg==E';

		$keyarmory = new KeyArmory( $this->key_id );

		$decrypted_data = $keyarmory->decrypt($encrypted_data);

		//echo $decrypted_data;

		$this->assertEquals($data, $decrypted_data);
	}




}
