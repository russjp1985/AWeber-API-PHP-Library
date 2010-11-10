<?php

class TestAWeberAPI extends UnitTestCase {

    public function setUp() {
        $this->adapter = new MockOAuthAdapter();
        $this->app = array(
            'key'    => 'RogsGzUw3QAK6cPSI24u',
            'secret' => '1eaHAFJnEklS8qSBvitvSO6OCkaU4QyHU3AOE1rw',
        );
        $this->aweber = new AWeberAPI($this->app['key'],
            $this->app['secret']);

        $this->user = array(
            'token'  => 'lc0UcVJdlpNyVVMLzeZWZZGb61pEnlhBdHGg9usF',
            'secret' => 'VMus5FW1TyX7N24xaOyc0VsylGBHC6rAomq3LM67',
        );

    }

    /**
     * App keys given at construction should be maintained internally
     */
    public function test_should_contain_app_keys() {
        $this->assertEqual($this->aweber->consumerKey,
                           $this->app['key']);
        $this->assertEqual($this->aweber->consumerSecret,
                           $this->app['secret']);

    }

    /**
     * OAuther adapter object should be allowed to be switched out
     */
    public function test_should_allow_setting_oauth_adapter() {
        $this->aweber->setAdapter($this->adapter);
        $this->assertEqual($this->aweber->adapter, $this->adapter);
    }

    /**
     * When authorization fails, an exception is raised
     */
    public function test_should_raise_exception_if_auth_fails() {
        MockData::$oauth = false;
        $this->aweber->setAdapter($this->adapter);
        try {
            $account = $this->aweber->getAccount($this->user['token'],
                $this->user['secret']);
            $this->assertTrue(false, 'This should not run due to an exception');
        }
        catch (Exception $e) {
            $this->assertTrue(is_a($e, 'Exception'));
        }
        MockData::$oauth = true;
    }

    public function test_should_return_null_after_authorization() {
        $this->aweber->setAdapter($this->adapter);
        $account = $this->aweber->getAccount($this->user['token'],
            $this->user['secret']);
        $list = $account->lists->getById(123456);
        $this->assertTrue(empty($list));
    }

    /**
     * getAccount should load an AWeberEntry based on a single account
     * for the authorized user
     */
    public function test_getAccount() {
        $this->aweber->setAdapter($this->adapter);
        $account = $this->aweber->getAccount($this->user['token'],
            $this->user['secret']);
        $this->assertNotNull($account);
        $this->assertTrue(is_a($account, 'AWeberResponse'));
        $this->assertTrue(is_a($account, 'AWeberEntry'));
    }

    /**
     * Load from URL should take a relative URL and return the correct
     * object based on that request. Allows skipping around the tree
     * based on URLs, not just walking it.
     */
    public function test_loadFromUrl() {
        $this->aweber->setAdapter($this->adapter);
        $list = $this->aweber->loadFromUrl('/accounts/1/lists/303449');

        $this->assertTrue(is_a($list, 'AWeberEntry'));
        $this->assertEqual($list->type, 'list');
        $this->assertEqual($list->id, '303449');
    }

}
?>
