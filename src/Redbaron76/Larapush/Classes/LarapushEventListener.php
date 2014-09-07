<?php namespace Redbaron76\Larapush\Classes;

use Ratchet\Wamp\WampServerInterface;
use Ratchet\ConnectionInterface as Watcher;

class LarapushEventListener implements WampServerInterface {

	/**
	 * An instance of the LarapushBroadcaster class
	 * 
	 * @var Redbaron76\Classes\LarapushBroadcaster
	 */
	protected $broadcaster;

	/**
	 * An instance of the LarapushStorage class
	 * 
	 * @var Redbaron76\Classes\LarapushStorage
	 */
	protected $storage;

	/**
	 * Class constructor
	 */
	public function __construct(LarapushBroadcaster $broadcaster, LarapushStorage $storage)
	{
		$this->broadcaster = $broadcaster;
		$this->storage = $storage;
	}

	// WampServerInterface

	/**
	 * [onCall description]
	 * @param  Watcher $watcher [description]
	 * @param  [type]     $id         [description]
	 * @param  [type]     $channel    [description]
	 * @param  array      $params     [description]
	 * @return [type]                 [description]
	 */
	public function onCall(Watcher $watcher, $id, $channel, array $params)
	{
		// In this application if clients send data it's because the user hacked around in console
		// $watcher->callError($id, $channel, 'You are not allowed to make calls')->close();
		echo "onSubscribe id: $id | channel: $channel\n";
	}

	/**
	 * [onSubscribe description]
	 * @param  Watcher $watcher [description]
	 * @param  [type]     $channel    [description]
	 * @return [type]                 [description]
	 */
	public function onSubscribe(Watcher $watcher, $channel)
	{
		$this->broadcaster->addWatchedChannel($watcher, $channel);
		echo "onSubscribe\n";
		echo var_dump($channel) . "\n";
		echo "clients\n";
		foreach ($this->storage->watchers as $key => $client) {
			var_dump($key, $client);
		}
		echo var_dump($this->storage->laravels) . "\n";
		echo "\n\n\n\n\n\n";
	}

	/**
	 * [onUnSubscribe description]
	 * @param  Watcher $watcher [description]
	 * @param  [type]     $channel    [description]
	 * @return [type]                 [description]
	 */
	public function onUnSubscribe(Watcher $watcher, $channel)
	{
		echo "onUnSubscribe\n";
		echo var_dump($channel) . "\n";
	}

	/**
	 * Azionato quando un client fa 'conn.publish'
	 * @param  Watcher $watcher [description]
	 * @param  [type]     $channel    [description]
	 * @param  [type]     $event      [description]
	 * @param  array      $exclude    [description]
	 * @param  array      $eligible   [description]
	 * @return [type]                 [description]
	 */
	public function onPublish(Watcher $watcher, $channel, $event, array $exclude, array $eligible)
	{
		echo "onPublish\n";
		// echo "iterators: " . var_dump($channel->getIterator()) . "\n";
		// echo "channel: " . var_dump($channel) . "\n";
		// echo "event: ". var_dump($event) . "\n";
		// echo "exclude: " . var_dump($exclude) . "\n";
		// echo "eligible: " . var_dump($eligible) . "\n";
		$this->broadcaster->broadcastClientToClient($channel, $event, $exclude);
	}

	// ComponentInterface -> WampServerInterface

	/**
	 * onClose a connection, detach it from storage
	 * @param  Watcher $watcher
	 * @return void
	 */
	public function onClose(Watcher $watcher)
	{
		$this->storage->detach($watcher);
		echo "onClose\n";
		echo "tot clients: " . count($this->storage->watchers) . "\n";
		echo "clients\n";
		foreach ($this->storage->watchers as $key => $client) {
			var_dump($key, $client);
		}
		echo var_dump($this->storage->laravels) . "\n";
		echo "\n\n\n\n\n\n";
	}

	/**
	 * onOpen a new connection, attach it to storage
	 * @param  Watcher $watcher
	 * @return void
	 */
	public function onOpen(Watcher $watcher)
	{
		$this->storage->attach($watcher);
		echo "onOpen\n";
		echo "tot clients: " . count($this->storage->watchers) . "\n";
		echo "\n\n";
	}

	/**
	 * [onError description]
	 * @param  Watcher $watcher [description]
	 * @param  Exception  $e          [description]
	 * @return [type]                 [description]
	 */
	public function onError(Watcher $watcher, \Exception $e)
	{
		echo "onError " . $e->getMessage() . "\n";
		echo var_dump($e);
	}

}