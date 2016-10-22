<?php
/**
 * This file is part of the KeyArmory API Software Development Kit for PHP 5.5+.
 *
 * License: ISC
 *
 * The license can be found at the root directory of this package.
 */

namespace KeyArmory;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * KeyArmory API Class
 *
 * @package KeyArmory
 */
class KeyArmory
{
    /**
     * API url
     */
    const API_URL = 'https://api.keyarmory.com';

    /**
     * API version token
     */
    const API_VERSION = 'v1';

    /**
     * Client Implementation
     *
     * @var Client|ClientInterface
     */
    private $client;

    /**
     * API Key
     *
     * @var string
     */
    private $apiKey = '';

    /**
     * KeyArmory constructor.
     *
     * @param $apiKey
     * @param ClientInterface|null $client
     */
    public function __construct($apiKey, ClientInterface $client = null)
    {
        $this->client = $client instanceof ClientInterface
            ? $client
            : new Client();
        ;
    }

    /**
     * encrypts the given message and returns the remote identity of the message
     *
     * @param $message
     * @return string
     */
    public function encrypt($message)
    {
        $response = $this->client->get($this->composeUrl('encryption/token'), [
            'headers' => [
                'x-api-key' => $this->apiKey,
            ]
        ]);

        $payload = json_decode($response->getBody())->payload;
        $encrypted = $this->push($message, $payload->key);

        return sprintf('ka:%s:%s:%s', $payload->key_id, $payload->token, $encrypted);
    }

    /**
     * decrypts the remote identity assigned message and returns the decrypted string.
     *
     * @param string $remoteIdentity
     * @return string
     */
    public function decrypt($remoteIdentity)
    {
        list(, $keyId, $token, $encrypted) = explode(':', $message);

        $response = $this->client->get(
            $this->composeUrl(
                'encryption/token',
                [
                    'key_id' => $keyId,
                    'token' => $token,
                ]
            ),
            [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                ]
            ]
        );

        return $this->pull($encrypted, json_decode($response->getBody())->payload->key);
    }

    /**
     * composes the api url in charge using the given path and optional parameters.
     *
     * @param $path
     * @param array $parameters
     * @return string
     */
    public function composeUrl($path, array $parameters = [])
    {
        $path = trim($path, '/');

        $url = sprintf('%s/%s/%s', self::API_URL, self::API_VERSION, $path);

        if ( ! empty($parameters) ) {
            $url .= '?'.http_build_query($parameters);
        }

        return $url;
    }

    /**
     * pulls a RIJNDAEL-256 encryption and returns the plain representation of the decrypted data.
     *
     * @param $data
     * @param $key
     * @return string
     */
    protected function pull($data, $key)
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $ciphertext_dec = base64_decode($data);
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
    }

    /**
     * pushes a RIJNDAEL-256 encryption and returns the base64 representation of the encrypted data.
     *
     * @param $data
     * @param $key
     * @return string
     */
    protected function push($data, $key)
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv);
        $ciphertext = $iv . $ciphertext;
        return base64_encode($ciphertext);
    }
}