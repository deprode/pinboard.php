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
    $posts = $pinboard->recentPosts();
} catch (Exception $e) {
    print_r($e->getMessage());
    exit;
}

var_dump($posts);
// [
//     'date' => "2020-05-20T00:54:47Z",
//     'user' => 'testuser',
//     'posts' => [
//         [
//             'href' => 'https://example.com/',
//             'description' => 'long description',
//             'extended' => '',
//             'meta' => '09876543210987654321098765432109',
//             'hash' => '12345678901234567890123456789012',
//             'time' => "2020-04-14T11:51:06Z",
//             'shared' =>'yes',
//             'toread' => 'yes',
//             'tags' => 'Testing',
//         ]
//     ]
// ]
```
