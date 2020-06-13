<?php


use PinboardPHP\Lib\OptionValidation;
use PHPUnit\Framework\TestCase;

class OptionValidationTest extends TestCase
{
    public $v;

    protected function setUp(): void
    {
        parent::setUp();

        $this->v = new OptionValidation();
    }

    public function testValidate()
    {
        $this->assertFalse($this->v->validate([], []));
        $this->assertFalse($this->v->validate(['not_allow' => 'xxx'], ['not_allow' => 'not_allow_type']));
    }

    public function testGetErrors()
    {
        $this->v->validate(['tags' => bin2hex(random_bytes(128)), 'url' => 'foo-bar'], ['tags' => 'tag', 'url' => 'url']);
        $this->assertEquals(['tags' => ['tag'], 'url' => ['url']], $this->v->getErrors());
    }

    public function testTag()
    {
        $this->assertTrue($this->v->validate(['tags' => bin2hex(random_bytes(128))], ['tags' => 'tag']));
        $this->assertFalse($this->v->validate(['tags' => 'test'], ['tags' => 'tag']));
    }

    public function testUrl()
    {
        $this->assertTrue($this->v->validate(['url' => 'some_foo_bar'], ['url' => 'url']));
        $this->assertTrue($this->v->validate(['url' => 'scheme://example.com/'], ['url' => 'url']));
        $this->assertFalse($this->v->validate(['url' => 'https://example.com/'], ['url' => 'url']));
    }

    public function testTitle()
    {
        $this->assertTrue($this->v->validate(['description' => bin2hex(random_bytes(128))], ['description' => 'title']));
        $this->assertFalse($this->v->validate(['description' => bin2hex(random_bytes(127))], ['description' => 'title']));
    }

    public function testText()
    {
        $this->assertTrue($this->v->validate(['extended' => bin2hex(random_bytes(32768))], ['extended' => 'text']));
        $this->assertFalse($this->v->validate(['extended' => bin2hex(random_bytes(32767))], ['extended' => 'text']));
    }

    public function testDateTime()
    {
        $this->assertTrue($this->v->validate(['dt' => '2010-12-11 19:48:02'], ['dt' => 'datetime']));
        $this->assertFalse($this->v->validate(['dt' => '2010-12-11T19:48:02Z'], ['dt' => 'datetime']));
    }

    public function testDate()
    {
        $this->assertTrue($this->v->validate(['dt' => '2010/12/11'], ['dt' => 'date']));
        $this->assertFalse($this->v->validate(['dt' => '2010-12-11'], ['dt' => 'date']));
    }

    public function testYes()
    {
        $this->assertTrue($this->v->validate(['toread' => 'y'], ['toread' => 'yes']));
        $this->assertFalse($this->v->validate(['toread' => 'yes'], ['toread' => 'yes']));
    }

    public function testNo()
    {
        $this->assertTrue($this->v->validate(['toread' => 'n'], ['toread' => 'no']));
        $this->assertFalse($this->v->validate(['toread' => 'no'], ['toread' => 'no']));
    }

    public function testMd5()
    {
        $this->assertTrue($this->v->validate(['hash' => '0123456789ABCEF'], ['hash' => 'md5']));
        $this->assertTrue($this->v->validate(['hash' => '0123456789ABCEFGhijklmnopqrstuwx'], ['hash' => 'md5']));
        $this->assertFalse($this->v->validate(['hash' => '0123456789ABCEF01234567890ABCDEF'], ['hash' => 'md5']));
    }

    public function testInteger()
    {
        $this->assertTrue($this->v->validate(['count' => -1], ['count' => 'integer']));
        $this->assertTrue($this->v->validate(['count' => 2**32+1], ['count' => 'integer']));
        $this->assertFalse($this->v->validate(['count' => 100], ['count' => 'integer']));
    }

    public function testFormat()
    {
        $this->assertTrue($this->v->validate(['format' => 'html'], ['format' => 'format']));
        $this->assertFalse($this->v->validate(['format' => 'json'], ['format' => 'format']));
    }
}
