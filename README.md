This package is still **UNDER DEVELOPMENT** but feel free to try it as you wish.

## Larapush - WebSocket and Push server

##### a [Ratchet](http://socketo.me) and [ZMQ](http://zeromq.org) implementation for Laravel 4.

#### ZMQ is required
--------------------

Make sure to have **ZMQ installed** on your system before to try it. [Install guide](http://zeromq.org/bindings:php)



#### How to install this package
--------------------------------

```
// composer.json

{
    "require": {
        "redbaron76/larapush": "dev-master"
    }
}
```

```php
// app/config/app.php

'providers' => array(

		...

		'Redbaron76\Larapush\LarapushServiceProvider',
	),
```

Then run `composer update` to install the new package.



#### How to use
---------------

##### 1) Start the Larapush server

   From the console just type `php artisan larapush:serve` to rise the WebSocket/Ratchet server up.

##### 2) Use the Larapush facade in your routes to trigger events server-side

```php
// app/routes.php

Route::any('profile/{nickname}', ['as' => 'profile', function($nickname)
{
	Larapush::send(['message' => 'I watch you, '.$nickname.'!'], ['profileChannel'], 'profile.visit');

	return View::make('some.view');
}]);
```

##### 3) Subscribe your client to **channels** and listen for **events** in the client-side

![Server and client-side code](https://cloud.githubusercontent.com/assets/1061849/4200106/e8efe940-380c-11e4-8546-bda32652fa65.png)

![Results on browser](https://cloud.githubusercontent.com/assets/1061849/4200111/fdacdf0a-380c-11e4-9c91-0d71e7c99d26.png)

##### 4) Laravel - Ratchet session sync

In order to have Laravel session synced with the Ratchet server one, **YOU MUST** use specific Larapush filters in your routes:

1. Use `'before' => 'sessionRemove'` wherever you perform a **logout** action.
2. Use `'after' => 'sessionSync'` wherever you perform a **login** action and in **any authenticated route**.

This will maintain your sessions in sync and you'll be able to perform a target `Larapush::send()`.

![Use of Larapush filters](https://cloud.githubusercontent.com/assets/1061849/4200270/f52d8e68-380e-11e4-9c8d-c5d6af246bb0.png)

#### [Larapush.js](https://github.com/redbaron76/Larapush.js) - Pub/Sub js lib for Larapush
-------------------------------------------------------------------------------------------

In order to make your dev life easier with Larapush, please give [Larapush.js](https://github.com/redbaron76/Larapush.js) a try.

###### Follow my Twitter account [@FFumis](http://twitter.com/FFumis) for any update. 