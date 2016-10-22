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

		//print $url;
		$this->assertEquals(
			"https://api.keyarmory.com/v1/encryption/token?key_id=$key_id&token=$sample_token",
			$url,
			'The composed URL does not match expected URL structure'
		);
	}

    public function test_encryption() {
        $keyarmory = new \KeyArmory\KeyArmory('f0e4c2f76c58916ec258f246851bea091d14d4247a2fc3e18694461b1816e13b');

        $original_string = 'test';

        $encrypted_string = $keyarmory->encrypt($original_string);
        $unencrypted_string = $keyarmory->decrypt($encrypted_string);

        $this->assertEquals($unencrypted_string, $original_string);
    }

}