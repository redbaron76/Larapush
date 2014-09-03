<?php namespace Redbaron76\Larapush\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Larapush extends Facade {

	/**
	 * Get the registred name of the component
	 * 
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'Larapush'; }

}