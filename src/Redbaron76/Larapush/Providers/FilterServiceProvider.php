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
		$this->app->router->filter('larapushSync', self::$filterPath . 'LarapushFilter@larapushSync');
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