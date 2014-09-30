Phalcon\Mailer
==============

Mailer wrapper over SwiftMailer for Phalcon.

## Installing ##

- composer [phalcon/incubator](https://packagist.org/packages/phalcon/incubator) (install all incubator)*, or*
- composer [sksoft/phalcon-mailer](https://packagist.org/packages/sksoft/phalcon-mailer) (install only mailer component)


**Add in code:**

    require_once('vendor/autoload.php');

## Configure ##

**SMTP**

    $config = [
    	'driver' 	 => 'smtp',
    	'host'	 	 => 'smtp.gmail.com',
    	'port'	 	 => 465,
    	'encryption' => 'ssl',
    	'username'   => 'example@gmail.com',
    	'password'	 => 'your_password',
    	'from'		 => [
    			'email' => 'example@gmail.com',
    			'name'	=> 'YOUR FROM NAME'
    		]
    ];

**Sendmail**

    $config = [
    	'driver' 	 => 'sendmail',
		'sendmail' 	 => '/usr/sbin/sendmail -bs',
    	'from'		 => [
    		'email' => 'example@gmail.com',
    		'name'	=> 'YOUR FROM NAME'
    	]
    ];

**PHP Mail**

    $config = [
    	'driver' 	 => 'mail',
    	'from'		 => [
    		'email' => 'example@gmail.com',
    		'name'	=> 'YOUR FROM NAME'
    	]
    ];


## Example ##

### createMessage() ###

	$mailer = new \Phalcon\Mailer\Manager($config);
	
	$message = $mailer->createMessage()
			->to('example_to@gmail.com', 'OPTIONAL NAME')
			->subject('Hello world!')
			->content('Hello world!');

	// Set the Cc addresses of this message.
	$message->cc('example_cc@gmail.com');

	// Set the Bcc addresses of this message.
	$message->bcc('example_bcc@gmail.com');

	// Send message
	$message->send();

### createMessageFromView() ###

	/**
     * Global viewsDir for current instance Mailer\Manager.
     * 
     * This parameter is OPTIONAL, If it is not specified, 
	 * use DI from view service (getViewsDir)
     */
	$config['viewsDir'] = __DIR__ . '/views/email/';

	$mailer = new \Phalcon\Mailer\Manager($config);

	// view relative to the folder viewsDir (REQUIRED)
	$viewPath = 'email/example_message';

	// Set variables to views (OPTIONAL)
	$params [ 
		'var1' => 'VAR VALUE 1',
		'var2' => 'VAR VALUE 2',
		...
		'varN' => 'VAR VALUE N',
	];

	/**
	 * The local path to the folder viewsDir only this message. (OPTIONAL)
	 * 
	 * This parameter is OPTIONAL, If it is not specified, 
	 * use global parameter "viewsDir" from configuration.
	 */
	$viewsDirLocal = __DIR__ . '/views/email/local/';
	

	$message = $mailer->createMessageFromView($viewPath, $params, $viewsDirLocal)
			->to('example_to@gmail.com', 'OPTIONAL NAME')
			->subject('Hello world!');

	// Set the Cc addresses of this message.
	$message->cc('example_cc@gmail.com');

	// Set the Bcc addresses of this message.
	$message->bcc('example_bcc@gmail.com');

	// Send message
	$message->send();


## Events ##
- mailer:beforeCreateMessage
- mailer:afterCreateMessage
- mailer:beforeSend
- mailer:afterSend
- mailer:beforeAttachFile
- mailer:afterAttachFile
