# pinboard.php

## Install

`composer require deprode/pinboard.php=dev-master`

## Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use PinboardPHP\Lib\Client;

$pinboard = new Client('SET_YOUR_PINBOARD_TOKEN');

try {
    $posts = $pinboard->format($pinboard->recentPosts());
} catch (Exception $e) {
    print_r($e->getMessage());
    exit;
}

var_dump($posts);
```
