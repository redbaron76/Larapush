## Larapush - WebSocket and Push server

#### a [Ratchet](http://socketo.me) and [ZMQ](http://zeromq.org) implementation for Laravel 4.

This package is still **UNDER DEVELOPMENT** but feel free to download and try it as you wish.

##### a Laravel's flavoured Larapush facade usage:

```
// app/routes.php

Route::any('profile/{nickname}', ['as' => 'profile', function($nickname)
{
	Larapush::send(['message' => 'I watch you, '.$nickname.'!'], ['profileChannel'], 'profile.visit');

	return View::make('some.view');
}]);
```

#### [Larapush.js](https://github.com/redbaron76/Larapush.js) - Pub/Sub js lib for Larapush

In order to make your dev life easier with Larapush, please give [Larapush.js](https://github.com/redbaron76/Larapush.js) a try.

#### ZMQ is required

Make sure to have **ZMQ installed** on your system before to try it. [Install guide](http://zeromq.org/bindings:php)

Follow my Twitter account [@FFumis](http://twitter.com/FFumis) for any update. 