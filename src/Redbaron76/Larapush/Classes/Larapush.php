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
	 * Larapush class constructor
	 */
	public function __construct(Events $events)
	{
		$this->events = $events;
	}

	public function chat($with, $message, $where)
	{

	}

	/**
	 * Send message to ZMQ and fires a specific event
	 * 
	 * @param  array  		$message
	 * @param  string|array $channels
	 * @param  string 		$event
	 * @return void
	 */
	public function send($message, $channel = 'defaultChannel', $event = 'generic')
	{
		$socket = $this->getSocket();

		// Merge topic to the message and encode to json
		$message = json_encode(array_merge(['channel' => $channel, 'event' => $event], $message));

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