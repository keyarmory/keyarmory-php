<?php

namespace KeyArmory;

use \GuzzleHttp\Client;

class KeyArmory {

    private $api_key;
    private $base_url;

    function __construct($options) {
        //require '../vendor/autoload.php';

        $this->api_key = $options['api_key'];
        $this->base_url = 'https://api.keyarmory.com/v1';

        if (!$this->base_url) throw new \Exception('Key Armory API Key Required');
    }

    public function encrypt($data) {
        $http = new Client();

        $response = $http->get($this->base_url . '/encryption/token', [
            'headers' => [
                'x-api-key' => $this->api_key
            ]
        ]);

        $payload = json_decode($response->getBody())->payload;

        $encrypted_data = Util::encrypt($data, $payload->key);
        $encrypted_string = $payload->key_id . ':' . $payload->token . ':' . $encrypted_data;

        return $encrypted_string;
    }

    public function decrypt($encrypted_string) {
        $pieces = explode(':', $encrypted_string);
        $key_id = $pieces[0];
        $token = $pieces[1];
        $encrypted_data = $pieces[2];

        $http = new Client();

        $response = $http->post($this->base_url . '/encryption/key', [
            'headers' => [
                'x-api-key' => $this->api_key
            ],
            'form_params' => [
                'key_id' => $key_id,
                'token' => $token
            ]
        ]);

        $payload = json_decode($response->getBody())->payload;

        $decrypted_string = Util::decrypt($encrypted_data, $payload->key);

        return $decrypted_string;
    }

}

class Util {

    static public function encrypt($data, $key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv);
        $ciphertext = $iv . $ciphertext;
        return base64_encode($ciphertext);
    }

    static public function decrypt($data, $key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $ciphertext_dec = base64_decode($data);
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
    }

}