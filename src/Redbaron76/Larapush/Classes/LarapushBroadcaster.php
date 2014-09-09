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
	 * Suitable user targets
	 * 
	 * @var array
	 */
	protected $targets = [];

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
			$this->syncStorageAction($message);
		}
	}

	/**
	 * Set storage parameters for session sync
	 * 
	 * @param  string $message
	 * @return void
	 */
	private function syncStorageAction($message)
	{
		$message = json_decode($message, true);

		// Set the client session id to storage
		$this->storage->setSessionId($message['session_id']);

		if(array_key_exists('user_id', $message))
		{
			// Set the Laravel user id to store
			$this->storage->setUserId($message['user_id']);
		}

		if(array_key_exists('remove_id', $message))
		{
			// Remove the laravel user id from store
			$this->storage->removeUserId($message['remove_id']);
		}

		echo "message: \n";
		var_dump($message);
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

		$hasTarget = false;

		// We broadcast if we have audience only!
		if($this->checkChannelWatcher($message['channel']))
		{
			// Set user targets
			$this->targets = $message['user'];

			if(count($this->targets) > 0)
			{
				// Remove user targets from the message
				unset($message['user']);
				$hasTarget = true;
			}

			if(is_array($message['channel']))
			{
				foreach ($message['channel'] as $channel)
				{
					if(array_key_exists($channel, $this->storage->watchedChannels))
					{
						$watchedChannel = $this->storage->watchedChannels[$channel];						
						$this->sendToChannelOrTarget($hasTarget, $message, $watchedChannel);
					}
				}
			}
			else
			{
				$watchedChannel = $this->storage->watchedChannels[$message['channel']];
				$this->sendToChannelOrTarget($hasTarget, $message, $watchedChannel);
			}
		}
	}

	/**
	 * Check where to send a message
	 * 
	 * @param  bool   $hasTarget
	 * @param  array  $message
	 * @param  object $watchedChannel
	 * @return void
	 */
	private function sendToChannelOrTarget($hasTarget, $message, $watchedChannel)
	{
		if($hasTarget)
		{
			$this->sendToTarget($message, $watchedChannel);
		}
		else
		{
			$this->sendToChannel($message, $watchedChannel);
		}
	}

	/**
	 * Send a message to a channel (broadcast to all watchers)
	 * 
	 * @param  array  $message
	 * @param  object $watchedChannel
	 * @return void
	 */
	private function sendToChannel($message, $watchedChannel)
	{
		$watchedChannel->broadcast($message);
	}

	/**
	 * Send message to target on watched channel
	 * 
	 * @param  array  $message
	 * @param  object $watchedChannel
	 * @return void
	 */
	private function sendToTarget($message, $watchedChannel)
	{
		foreach($watchedChannel->getIterator() as $watcher)
		{
			if($this->suitableTarget($watcher->resourceId))
			{
				$watcher->event($watchedChannel, $message);
			}
		}
	}

	/**
	 * Check if a channel watcher is a suitable target
	 * 
	 * @param  int $resourceId - watcher resource id
	 * @return bool
	 */
	private function suitableTarget($resourceId)
	{
		return in_array(array_search($resourceId, $this->storage->laravels), $this->targets);
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