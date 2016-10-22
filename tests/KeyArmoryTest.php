<?php

use PHPUnit_Framework_TestCase as TestCase;

class KeyArmoryTest extends TestCase {

    public function testFullCircle() {
        $keyarmory = new \KeyArmory\KeyArmory('f0e4c2f76c58916ec258f246851bea091d14d4247a2fc3e18694461b1816e13b');

        $original_string = 'test';

        $encrypted_string = $keyarmory->encrypt($original_string);
        $unencrypted_string = $keyarmory->decrypt($encrypted_string);

        $this->assertEquals($unencrypted_string, $original_string);
    }

}