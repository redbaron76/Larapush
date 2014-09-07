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
	 * The Laravel auth user id
	 * 
	 * @var Auth::id()
	 */
	protected $user_id;

	/**
	 * An array of watchers (connected clients)
	 * 
	 * @var array
	 */
	public $watchers = [];

	/**
	 * Sync Laravel Session ID (or User Id) to WAMP $resourceId
	 * 
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

		$this->upsyncSession($resource_id);
		
		$this->watchers[$resource_id] = $watcher;
	}

	/**
	 * Update and Sync session/user Id
	 * to $watcher->resourceId in laracasts
	 *
	 * @param int $resource_id
	 * @return void
	 */
	private function upsyncSession($resource_id)
	{
		// search for changed session_id (after an Auth::attempt. maybe)
		$laravel_sessId = array_search($resource_id, $this->laravels);
		
		if($laravel_sessId)
		{
			if($this->session_id and ! $this->user_id)
			{
				// Remove if already present in $laravels
				if($laravel_sessId)
				{
					unset($this->laravels[$laravel_sessId]);
				}
				// set a fresh binding
				$this->laravels[$this->session_id] = $resource_id;
			}
			elseif($this->session_id and $this->user_id)
			{
				// Update laravels with user_id if not already present
				if( ! in_array($this->user_id, $this->laravels))
				{
					$this->laravels[$this->user_id] = $this->laravels[$this->session_id];
					unset($this->laravels[$this->laravels[$this->session_id]]);
				}			
			}
			else
			{
				unset($this->laravels[$laravel_sessId]);
			}
		}
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

	/**
	 * Set the $user_id attribute if Auth::check()
	 * Called in Broadcaster\pushMessageToServer
	 * 
	 * @param void
	 */
	public function setUserId($user_id)
	{
		$this->user_id = $user_id;
	}

}