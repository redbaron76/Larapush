<?php namespace Redbaron76\Larapush;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class LarapushServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Get an instance of AliasLoader
	 * 
	 * @return instance
	 */
	protected $aliasLoader;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('redbaron76/larapush');

		// Register Event Subscriber
		// $this->app->register('Redbaron76\Larapush\Providers\EventServiceProvider');

		// Register command on boot
		$this->commands('larapush:serve');

		// Facade alias
		$this->aliasLoader = AliasLoader::getInstance();

		$this->app->booting(function()
		{
			$this->aliasLoader->alias('Larapush', 'Redbaron76\Larapush\Support\Facades\Larapush');
		});	
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// IoC bindings
		$this->app->bind('LarapushBroadcaster', function()
		{
			$events = $this->app->make('events');
			$storage = $this->app->make('LarapushStorage');
			return new Classes\LarapushBroadcaster($events, $storage);
		});

		// MUST BE singleton in order to share Storage
		// between Server and Clients
		$this->app->singleton('LarapushStorage', function()
		{
			return new Classes\LarapushStorage();
		});

		$this->app->bind('LarapushServer', function()
		{
			$broadcaster = $this->app->make('LarapushBroadcaster');
			$storage = $this->app->make('LarapushStorage');
			return new Classes\LarapushServer($broadcaster, $storage);
		});

		$this->app->singleton('ZMQContext', function()
		{
			return new \ZMQContext();
		});

		// Larapush Facade
		$this->app['Larapush'] = $this->app->share(function($app)
		{
			$events = $this->app->make('events');
			$storage = $this->app->make('LarapushStorage');
			return new Classes\Larapush($events, $storage);
		});

		// IoC Command
		$this->app['larapush:serve'] = $this->app->share(function($app)
		{
			return new Support\Commands\LarapushServeCommand();
		});

		// Registering Service Providers
		$this->app->register('Redbaron76\Larapush\Providers\EventServiceProvider');
		$this->app->register('Redbaron76\Larapush\Providers\FilterServiceProvider');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}	

}
