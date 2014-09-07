<?php namespace Redbaron76\Larapush\Classes;

use Redbaron76\Larapush\Interfaces\LarapushStorageInterface;

class LarapushStorage implements LarapushStorageInterface {

	/**
	 * The client Laravel session id
	 * 
	 * @var string
	 */
	protected $session_id;

	/**
	 * An array of watchers (connected clients)
	 * 
	 * @var array
	 */
	public $watchers = [];

	/**
	 * Sync Laravel Session ID to WAMP $resourceId
	 * @var [type]
	 */
	public $laravels = [];

	/**
	 * Attach a WampConnection (synced with Laravel SessionId)
	 * to the $watchers (connected clients) array
	 * 
	 * @param  obj $watcher Ratchet\Wamp\WampConnection
	 * @return void
	 */
	public function attach($watcher)
	{
		$resource_id = $watcher->resourceId;

		if($this->session_id)
		{
			// search for changed session_id (after an Auth::attempt. maybe)
			$laravel_id = array_search($resource_id, $this->laravels);

			if($laravel_id)
			{
				unset($this->laravels[$laravel_id]);
			}

			// set a fresh binding
			$this->laravels[$this->session_id] = $resource_id;
		}
		
		$this->watchers[$resource_id] = $watcher;
	}

	/**
	 * Detach the closed connection (onClose)
	 * from the $watchers array
	 * 
	 * @param  obj $watcher Ratchet\Wamp\WampConnection
	 * @return void
	 */
	public function detach($watcher)
	{
		$resource_id = $watcher->resourceId;

		// Unset watcher from laravels[]
		$laravel_id = array_search($resource_id, $this->laravels);
		unset($this->laravels[$laravel_id]);
		
		// Unset watcher from watchers
		unset($this->watchers[$watcher->resourceId]);
	}

	/**
	 * Set the $session_id attribute
	 * Called in Broadcaster\pushMessageToServer
	 * 
	 * @param void
	 */
	public function setSessionId($session_id)
	{
		$this->session_id = $session_id;
	}

}