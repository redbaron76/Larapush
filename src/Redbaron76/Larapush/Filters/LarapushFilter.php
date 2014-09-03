<?php namespace Redbaron76\Larapush\Filters;

class LarapushFilter {
	
	/**
	 * Send Laravel's Session Id to server via ZMQ
	 * before client's websocket connection
	 * 
	 * @return void
	 */
	public function larapushSync()
	{
		\Larapush::sync(['session_id' => \Session::getId()]);
	}

}