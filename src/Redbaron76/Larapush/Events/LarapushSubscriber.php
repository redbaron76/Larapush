<?php namespace Redbaron76\Larapush\Events;

class LarapushSubscriber {
	
	private static $eventPath = 'Redbaron76\Larapush\Events\\';

	/**
	 * A generic event
	 * 
	 * @param  object $event
	 * @return void
	 */
	public function onGenericEvent($event)
	{
		echo "generic: ";
		var_dump($event);
	}

	public function onSyncSessionId($socket, $message)
	{
		// Send to ZeroMQ Server
		$socket->send($message);
	}

	public function onZmqBroadcast($broadcaster, $message)
	{
		// Broadcast message to subscribed clients
		$broadcaster->broadcastServerToClient($message);
	}

	public function onZmqSend($socket, $message)
	{
		// Send to ZeroMQ Server
		$socket->send($message);
	}

	/**
	 * Subscribes events
	 * 
	 * @param  mixed $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen('generic', self::$eventPath . 'LarapushSubscriber@onGenericEvent');
		$events->listen('sid.sync', self::$eventPath . 'LarapushSubscriber@onSyncSessionId');
		$events->listen('zmq.broadcast', self::$eventPath . 'LarapushSubscriber@onZmqBroadcast');
		$events->listen('zmq.send', self::$eventPath . 'LarapushSubscriber@onZmqSend');
	}

}