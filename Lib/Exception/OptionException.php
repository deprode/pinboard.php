<?php

namespace PinboardPHP\Lib\Exception;

use Exception;

class OptionException extends Exception {
    const TYPE_TAG = 'tag';
    const TYPE_URL = 'url';
    const TYPE_TITLE = 'title';
    const TYPE_TEXT = 'text';
    const TYPE_DATETIME = 'datetime';
    const TYPE_DATE = 'date';
    const TYPE_YES = 'yes';
    const TYPE_NO = 'no';
    const TYPE_MD5 = 'md5';
    const TYPE_INT = 'integer';
    const TYPE_FORMAT = 'format';

    protected $errors;
    protected $messages;

    public function __construct(array $errors, array $messages = null)
    {
        $this->errors = $errors;

        $this->messages = $messages ?? [
            self::TYPE_TAG => 'up to 255 characters. May not contain commas or whitespace',
            self::TYPE_URL => 'as defined by RFC 3986. Allowed schemes are http, https, javascript, mailto, ftp and file',
            self::TYPE_TITLE => 'up to 255 characters long',
            self::TYPE_TEXT => ' up to 65536 characters long',
            self::TYPE_DATETIME => 'UTC timestamp in this format: 2010-12-11T19:48:02Z',
            self::TYPE_DATE => 'UTC date in this format: 2010-12-11',
            self::TYPE_YES => "the literal string 'yes' or 'no'",
            self::TYPE_NO => "the literal string 'yes' or 'no'",
            self::TYPE_MD5 => '32 character hexadecimal MD5 hash',
            self::TYPE_INT => 'integer in the range 0..2^32',
            self::TYPE_FORMAT => "the literal string 'json' or 'xml'",
        ];
    }

    protected function getErrorMessage($type)
    {
        return $this->messages[$type] ?? 'An error occurred during validation';
    }

    public function getErrorMessages()
    {
        $error_messages = [];
        foreach ($this->errors as $name => $error) {
            $messages = [];
            foreach ($error as $type) {
                $messages[] = $this->getErrorMessage($type);
            }
            $error_messages[$name] = $messages;
        }

        return $error_messages;
    }
}