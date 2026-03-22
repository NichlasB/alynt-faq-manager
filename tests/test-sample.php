<?php

use PHPUnit\Framework\TestCase;

final class Test_Sample extends TestCase {
    public function test_plugin_constants_defined() {
        $this->assertTrue( defined( 'ALYNT_FAQ_VERSION' ) );
    }
}
