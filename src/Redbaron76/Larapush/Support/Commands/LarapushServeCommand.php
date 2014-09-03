<?php namespace Redbaron76\Larapush\Support\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LarapushServeCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'larapush:serve';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Start a Larapush WebSocket server';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$server = \App::make('LarapushServer');

		$this->info('Larapush is now listening on port ' . $this->option('port'));

		$server->run($this->option('port'));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			// array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['port', 'p', InputOption::VALUE_OPTIONAL, 'The Port on which we listen for new connections', \Config::get('larapush::socketPort')],
		];
	}

}
