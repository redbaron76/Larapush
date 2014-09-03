<?php namespace Redbaron76\Larapush\Providers;

use Illuminate\Support\ServiceProvider;
use Redbaron76\Larapush\Events\LarapushSubscriber;

class EventServiceProvider extends ServiceProvider {

	/**
	 * Register this service provider
	 * 
	 * @return void
	 */
	public function register()
	{
		$this->app->events->subscribe(new LarapushSubscriber);
	}

	/**
	 * Boot this service provider
	 * 
	 * @return void
	 */
	public function boot()
	{
		// 
	}

}