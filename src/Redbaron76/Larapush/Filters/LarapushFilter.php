<?php namespace Redbaron76\Larapush\Filters;

class LarapushFilter {
	
	/**
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
			$arr = array_merge($arr, ['user_id' => \Auth::id()]);
		} 

		\Larapush::sync($arr);
	}

}