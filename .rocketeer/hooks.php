<?php return array(

	// Tasks
	//
	// Here you can define in the `before` and `after` array, Tasks to execute
	// before or after the core Rocketeer Tasks. You can either put a simple command,
	// a closure which receives a $task object, or the name of a class extending
	// the Rocketeer\Abstracts\AbstractTask class
	//
	// In the `custom` array you can list custom Tasks classes to be added
	// to Rocketeer. Those will then be available in the command line
	// with all the other tasks
	//////////////////////////////////////////////////////////////////////

	// Tasks to execute before the core Rocketeer Tasks
	'before' => array(
		'setup'   => array(),
		'deploy'  => array(),
		'cleanup' => array(),
	),

	// Tasks to execute after the core Rocketeer Tasks
	'after'  => array(
		'setup'   => array(),
		'deploy'  => array(
			'git clone git@bitbucket.org:ubirimi137/configurations.git',
			'echo Setting up configurations\n',
			'mv configurations/products/config.properties app/config/config.properties',
			'mv configurations/products/phinx.yml phinx.yml',
			'echo Removing cache\n',
			'rm -rf /home/products/cache',
			'mkdir -p /home/products/cache',
			'Setting up permissions\n',
			'chown www-data:www-data -R /home/products/cache',
			'echo Running migrations\n',
			'php vender/bin/phinx migrate -e production'
		),
		'cleanup' => array(
			'echo Cleaning up\n',
			'rm -rf /home/products/.rocketeer',
			'rm -rf configurations',
		),
	),

	// Custom Tasks to register with Rocketeer
	'custom' => array(),
);