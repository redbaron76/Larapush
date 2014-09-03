<?php namespace Redbaron76\Larapush\Interfaces;

interface LarapushStorageInterface {

	public function attach($watcher);
	public function detach($watcher);	
	
}