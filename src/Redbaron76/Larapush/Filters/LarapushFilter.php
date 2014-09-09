<?php namespace Redbaron76\Larapush\Filters;

class LarapushFilter {
	
	/**
	 * To trigger AFTER Auth login
	 * 
	 * Send Laravel's Session Id and User Id to server
	 * via ZMQ before client's websocket connection
	 * 
	 * @return void
	 */
	public function sessionSync()
	{
		$arr = ['session_id' => \Session::getId()];

		if(\Auth::check())
		{
			$arr = ['session_id' => \Session::getId(), 'user_id' => \Auth::id()];
		}

		\Larapush::sync($arr);
	}

	/**
	 * To Trigger BEFORE Auth logout
	 * 
	 * Send Laravel's Session Id and User Id to server
	 * via ZMQ before client's websocket connection
	 * 
	 * @return [type] [description]
	 */
	public function sessionRemove()
	{
		$arr = ['session_id' => \Session::getId()];

		if(\Auth::check())
		{
			$arr = ['session_id' => \Session::getId(), 'remove_id' => \Auth::id()];
		}

		\Larapush::sync($arr);
	}

}