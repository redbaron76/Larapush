<?php namespace Redbaron76\Larapush\Providers;

use Illuminate\Support\ServiceProvider;

class FilterServiceProvider extends ServiceProvider {

	private static $filterPath = 'Redbaron76\Larapush\Filters\\';

	/**
	 * Register this service provider
	 * 
	 * @return void
	 */
	public function register()
	{
		$this->app->router->filter('sessionSync', self::$filterPath . 'LarapushFilter@sessionSync');
		$this->app->router->filter('sessionRemove', self::$filterPath . 'LarapushFilter@sessionRemove');
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