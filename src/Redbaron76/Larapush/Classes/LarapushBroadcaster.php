<?php namespace Redbaron76\Larapush\Classes;

use Illuminate\Events\Dispatcher as Events;
use Redbaron76\Larapush\Interfaces\LarapushBroadcasterInterface;

class LarapushBroadcaster implements LarapushBroadcasterInterface {

	/**
	 * An Instance of Events\Dispatcher
	 * 
	 * @var Illuminate\Events\Dispatcher
	 */
	protected $events;

	/**
	 * LarapushStorage instance
	 * 
	 * @var Redbaron76\Larapush\Classes\LarapushStorage
	 */
	protected $storage;

	/**
	 * Class constructor
	 */
	public function __construct(Events $events, LarapushStorage $storage)
	{
		$this->events = $events;
		$this->storage = $storage;
	}

	/**
	 * Callback on Server when receiving message from the ZMQContext
	 * 
	 * @param  $message passed
	 * @return void
	 */
	public function pushMessageToServer($message)
	{
		if(substr_count($message, '{"session_id":"') == 0)
		{
			$this->events->fire('zmq.broadcast', [$this, $message]);
		}
		else
		{			
			$message = json_decode($message, true);
			// Set the client session id to storage
			$this->storage->setSessionId($message['session_id']);

			if(in_array('user_id', $message))
			{
				$this->storage->setUserId($message['user_id']);
			}			
		}
	}

	/**
	 * Broadcasts a message to watchers from client to client
	 * 
	 * @param  string $channel
	 * @param  string $message - json encoded object
	 * @param  array  $exclude - watchers to exclude
	 * @return void
	 */
	public function broadcastClientToClient($channel, $message, $exclude)
	{
		if(empty($exclude))
		{
			$channel->broadcast($message);
		}
		else
		{
			foreach ($channel->getIterator() as $watcher)
			{
				if ( ! in_array($watcher->WAMP->sessionId, $exclude))
				{
					$watcher->event($channel, $message);
				}
			}
		}

		if(array_key_exists('fire', $message))
		{
			$this->events->fire($message['fire'], [$message]);
		}
	}

	/**
	 * Broadcasts a message to watched channels
	 * 
	 * @param  string $message
	 * @return void
	 */
	public function broadcastServerToClient($message)
	{
		// Decode serialized message into array
		$message = json_decode($message, true);

		// We broadcast if we have audience only!
		if($this->checkChannelWatcher($message['channel']))
		{
			// Set user targets
			$targets = $message['user'];

			// We have targets so filter WampConnections
			if(count($targets) > 0)
			{
				// remove users from message
				unset($message['user']);

				if(is_array($message['channel']))
				{
					foreach ($message['channel'] as $channel)
					{
						if(array_key_exists($channel, $this->storage->watchedChannels))
						{
							$watchedChannel = $this->storage->watchedChannels[$channel];
							
							foreach($watchedChannel->getIterator() as $watcher)
							{
								if(in_array(array_search($watcher->resourceId, $this->storage->laravels), $targets))
								{
									$watcher->event($watchedChannel, $message);
								}
							}
						}
					}
				}
				else
				{
					$watchedChannel = $this->storage->watchedChannels[$message['channel']];

					foreach($watchedChannel->getIterator() as $watcher)
					{
						// Check if the laravel Id in laravels related to channel watcher is in $targets 
						if(in_array(array_search($watcher->resourceId, $this->storage->laravels), $targets))
						{
							$watcher->event($watchedChannel, $message);
						}
					}
				}
			}
			else
			{
				if(is_array($message['channel']))
				{
					foreach ($message['channel'] as $channel)
					{
						if(array_key_exists($channel, $this->storage->watchedChannels))
						{
							$watchedChannel = $this->storage->watchedChannels[$channel];
							$watchedChannel->broadcast($message);
						}
					}
				}
				else
				{
					$watchedChannel = $this->storage->watchedChannels[$message['channel']];
					$watchedChannel->broadcast($message);
				}
			}
		}
	}

	/**
	 * Check if any channel has at least one watcher
	 * 
	 * @param  array|string $channels
	 * @return bool
	 */
	private function checkChannelWatcher($channels)
	{
		$watched = $this->storage->watchedChannels;

		if(is_string($channels) and array_key_exists($channels, $watched))
		{
			return true;
		}
		elseif(is_array($channels))
		{
			foreach ($channels as $channel)
			{
				if(array_key_exists($channel, $watched))
				{
					return true;
				}
			}
		}

		return false;
	}	

}