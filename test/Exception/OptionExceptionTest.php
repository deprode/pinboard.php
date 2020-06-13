<?php


use PHPUnit\Framework\TestCase;
use PinboardPHP\Lib\Exception\OptionException;

class OptionExceptionTest extends TestCase
{
    public function testGetErrorMessages()
    {
        $exception = new OptionException(['tag' => ['tag', 'md5']]);
        $this->assertEquals(['tag' => ['up to 255 characters. May not contain commas or whitespace', '32 character hexadecimal MD5 hash']], $exception->getErrorMessages());

        $exception = new OptionException(['meme' => ['title'], 'yesno' => ['no']]);
        $this->assertEquals(['meme' => ['up to 255 characters long'], 'yesno' => ['the literal string \'yes\' or \'no\'']], $exception->getErrorMessages());

        $exception = new OptionException(['foo' => ['bar']]);
        $this->assertEquals(['foo' => ['An error occurred during validation']], $exception->getErrorMessages());
    }
}