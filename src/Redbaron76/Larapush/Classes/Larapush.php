<?php namespace Redbaron76\Larapush\Classes;

use Illuminate\Events\Dispatcher as Events;

class Larapush extends LarapushConnection {

	/**
	 * Laravel Events instance
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
	 * Larapush class constructor
	 */
	public function __construct(Events $events, LarapushStorage $storage)
	{
		$this->events = $events;
		$this->storage = $storage;
	}

	/**
	 * Send message to Laravel's user(s) id
	 * 
	 * @param  array  $with    - an array of laravel user id
	 * @param  array  $message - an array of data (will be jsoned)
	 * @param  string $where   - a channel
	 * @param  string $event   - an event
	 * @return void
	 */
	public function chat($with, $message, $where = 'chat.channel', $event = 'chat.event')
	{
		$this->send(['message' => $message], $where, 'chat.event', $with);
	}

	/**
	 * Send message to ZMQ and fires a specific event
	 * 
	 * @param  array  		$message
	 * @param  string|array $channels
	 * @param  string 		$event
	 * @param  array        $user
	 * @return void
	 */
	public function send($message, $channel = 'channel', $event = 'generic', $user = [])
	{
		$socket = $this->getSocket();

		// Merge topic to the message and encode to json
		$message = json_encode(array_merge(['channel' => $channel, 'event' => $event, 'user' => $user], $message));

		// Fire events
		$this->events->fire($event, [$message]);
		$this->events->fire('zmq.send', [$socket, $message]);		
	}

	/**
	 * Send Laravel's Session Id to server via ZMQ
	 * before client's websocket connection
	 * 
	 * @return void
	 */
	public function sync($message)
	{
		$socket = $this->getSocket();

		$message = json_encode($message);

		// Fire events
		$this->events->fire('sid.sync', [$socket, $message]);
	}	

}