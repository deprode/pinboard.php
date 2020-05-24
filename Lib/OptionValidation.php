<?php


namespace PinboardPHP\Lib;


class OptionValidation
{
    public function validate(array $options = [], array $types = [])
    {
        $isValid = false;
        foreach ($types as $name => $type)
        {
            if ($this->isAllowType($type) === false || array_key_exists($name, $options) === false) {
                continue;
            }
            $isValid |= !($this->$type($options[$name]));
        }

        return (bool)$isValid;
    }

    private function isAllowType(string $type)
    {
        return in_array($type, ['tag', 'url', 'title', 'text', 'datetime', 'date', 'yes', 'no', 'md5', 'integer', 'format']);
    }

    protected function tag(string $tags)
    {
        // up to 255 characters. May not contain commas or whitespace.
        return strlen($tags) < 255;
    }

    protected function url(string $url)
    {
        // as defined by RFC 3986. Allowed schemes are http, https, javascript, mailto, ftp and file.
        // The Safari-specific feed scheme is allowed but will be treated as a synonym for http.
        $allowed = ['http', 'https', 'javascript', 'mailto', 'ftp', 'file'];
        $parsed = parse_url($url);
        return $parsed && in_array($parsed['scheme'], $allowed);
    }

    protected function title(string $title)
    {
        // up to 255 characters long
        return strlen($title) < 255;
    }

    protected function text(string $text)
    {
        // up to 65536 characters long.
        return strlen($text) < 65536;
    }

    protected function datetime(string $datetime)
    {
        // UTC timestamp in this format: 2010-12-11T19:48:02Z.
        // Valid date range is Jan 1, 1 AD to January 1, 2100 (but see note below about future timestamps).
        $d = date_create_from_format('Y-m-d\TH:i:s\Z', $datetime);
        return $d && $d->format('Y-m-d\TH:i:s\Z') == $datetime;
    }

    protected function date(string $date)
    {
        // UTC date in this format: 2010-12-11. Same range as datetime above
        $d = date_create_from_format('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

    protected function yes(string $option = '')
    {
        // the literal string 'yes' or 'no'
        return $option === 'yes' || $option === 'no';
    }

    protected function no(string $option = '')
    {
        // the literal string 'yes' or 'no'
        return $option === 'yes' || $option === 'no';
    }

    protected function md5(string $option = '')
    {
        // 32 character hexadecimal MD5 hash
        return strlen($option) === 32 && ctype_xdigit($option);
    }

    protected function integer(int $option = -1)
    {
        // integer in the range 0..2^32
        return 0 <= $option && $option <= 2**32;
    }

    protected function format(string $option = '')
    {
        // the literal string 'json' or 'xml'
        return $option === 'json' || $option === 'xml';
    }
}