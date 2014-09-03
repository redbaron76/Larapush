<?php namespace Redbaron76\Larapush\Classes;

use React\ZMQ\Context as ZMQContext;
use React\EventLoop\Factory as ReactLoop;
use React\Socket\Server as SocketServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;

class LarapushServer {

	/**
	 * LarapushBroadcaster instance
	 * 
	 * @var Redbaron76\Larapush\Classes\LarapushBroadcaster
	 */
	protected $broadcaster;

	/**
	 * LarapushStorage instance
	 * 
	 * @var Redbaron76\Larapush\Classes\LarapushStorage
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

	/**
	 * Run the Larapush Server
	 * 
	 * @param  $port number
	 * @return void
	 */
	public function run($port)
	{
		$loop = ReactLoop::create();

		// ZMQ Context pulling ZeroMQ connections from the SERVER
		$context = new ZMQContext($loop);
		
		// Waiting for ZeroMQ messages from server-side
		$pull = $context->getSocket(\ZMQ::SOCKET_PULL, \Config::get('larapush::pers_socket_name'));
		$pull->bind(\Config::get('larapush::zmqConnect') . ':' . \Config::get('larapush::zmqPort'));

		// On message, execute pushMessageToServer($message) in LarapushBroadcaster
		$pull->on('message', [$this->broadcaster, 'pushMessageToServer']);

		// WebSocket server for CLIENTS waiting for real-time updates
		$webSocket = new SocketServer($loop);
		$webSocket->listen($port, \Config::get('larapush::socketConnect'));

		$webServer = 	new IoServer(
							new HttpServer(
								new WsServer(
									new WampServer(
										new LarapushEventListener(
											$this->broadcaster,
											$this->storage
										)
									)
								)
							), $webSocket
					 	);

		$loop->run();
	}

}