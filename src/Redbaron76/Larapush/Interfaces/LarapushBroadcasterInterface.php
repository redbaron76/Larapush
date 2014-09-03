<?php namespace Redbaron76\Larapush\Interfaces;

interface LarapushBroadcasterInterface {

	public function broadcastClientToClient($channel, $message, $exclude);
	public function broadcastServerToClient($message);
	public function pushMessageToServer($message);	
	
}