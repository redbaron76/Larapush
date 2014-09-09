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
	 * The Laravel auth user id to remove from laravels
	 * 
	 * @var Auth::id()
	 */
	protected $remove_id;

	/**
	 * A flag to remove or not user
	 * 
	 * @var boolean
	 */
	protected $remove = false;

	/**
	 * Sync Laravel Session ID (or User Id) to WAMP $resourceId
	 * 
	 * @var [type]
	 */
	public $laravels = [];

	/**
	 * $user Ids to send messages to
	 * 
	 * @var array
	 */
	public $targets = [];

	/**
	 * A store for all the channels clients are watching
	 * 
	 * @var array
	 */
	public $watchedChannels = [];

	/**
	 * An array of watchers (connected clients)
	 * 
	 * @var array
	 */
	public $watchers = [];

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

		if($this->upsyncSession($resource_id))
		{
			$this->watchers[$resource_id] = $watcher;
		}

		$this->cleanUpLaravels();

		// echo "watchers:\n";
		// var_dump($this->watchers);
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
		$laravel_id = $this->getLaravelsKey($resource_id);
		unset($this->laravels[$laravel_id]);
		
		// Unset watcher from watchers
		unset($this->watchers[$watcher->resourceId]);
	}

	/**
	 * Update and Sync session/user Id
	 * to $watcher->resourceId in laravels
	 *
	 * @param int $resource_id
	 * @return bool
	 */
	private function upsyncSession($resource_id)
	{		
		if( ! $this->remove_id and ! $this->user_id and $this->session_id)
		{
			$this->laravels[$this->session_id] = $resource_id;
			// echo "set session_id in laravels\n\n";
		}

		if( ! $this->remove_id and $this->user_id and $this->session_id)
		{
			$this->laravels[$this->user_id] = $resource_id;
			// echo "set user_id in laravels\n\n";
		}

		if( ! $this->user_id and $this->remove_id and $this->session_id)
		{
			$this->laravels[$this->session_id] = $resource_id;
			unset($this->laravels[$this->remove_id]);
			// echo "remove user_id in laravels\n\n";
		}

		// echo "laravels:\n";
		// var_dump($this->laravels);
		
		return true;
	}

	/**
	 * Cleanup dead laravels associations
	 * 
	 * @return void
	 */
	private function cleanUpLaravels()
	{
		$watchersKeys = array_keys($this->watchers);

		foreach ($this->laravels as $key => $laravel)
		{
			if( ! in_array($laravel, $watchersKeys))
			{
				unset($this->laravels[$key]);
			}
		}

		$this->user_id = null;
		$this->remove_id = null;
		$this->remove = false;
	}

	/**
	 * Get the key of the array in $laravels
	 * 
	 * @param  int     $resource_id
	 * @return string
	 */
	public function getLaravelsKey($resource_id)
	{
		return array_search($resource_id, $this->laravels);
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
		$this->remove_id = null;
		$this->remove = false;
	}

	/**
	 * Set the $user_id attribute if Auth::logout()
	 * and flag remove as true.
	 * Called in Broadcaster\pushMessageToServer
	 * 
	 * @param void
	 */
	public function removeUserId($user_id)
	{
		$this->remove_id = $user_id;
		$this->user_id = null;
		$this->remove = true;
	}

	/**
	 * Add a watched channel to watched channels store
	 * 
	 * @param Ratchet\Wamp\Connection
	 * @param Ratchet\Wamp\Topic
	 * @return void
	 */
	public function addWatchedChannel($connection, $channel)
	{
		if( ! array_key_exists($channel->getId(), $this->watchedChannels))
		{
			$this->watchedChannels[$channel->getId()] = $channel;
		}
	}
	
	/**
	 * Reset watched channels from store
	 * 
	 * @return void
	 */
	public function resetWatchedChannels()
	{
		$this->watchedChannels = [];
	}

}