<?php namespace Redbaron76\Larapush\Classes;

abstract class LarapushConnection {
	
	/**
	 * ZMQSocket instance
	 * 
	 * @var ZMQSocket $socket
	 */
	protected $socket;

	/**
	 * Connect to ZMQ and set socket
	 * 
	 * @return ZMQ $socket instance
	 */
	protected function connectToZMQ()
	{
		$context = \App::make('ZMQContext');

		$this->socket = $context->getSocket(\ZMQ::SOCKET_PUSH, \Config::get('larapush::persistent_socket_name'));
		$this->socket->connect($this->getPusherConnect());

		return $this->socket;
	}

	/**
	 * Get the opened socket or connect to ZMQContext
	 * @return $socket instance
	 */
	protected function getSocket()
	{
		return isset($this->socket) ? $this->socket : $this->connectToZMQ();
	}

	/**
	 * Get the pusher connect string
	 * 
	 * @return string
	 */
	protected function getPusherConnect()
	{
		return \Config::get('larapush::zmqConnect') . ':' . \Config::get('larapush::zmqPort');
	}

}