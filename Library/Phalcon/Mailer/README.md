Phalcon\Mailer
==============

Mailer wrapper over SwiftMailer for Phalcon.

## Configure ##

**SMTP**

    $config = [
    	'driver' 	 => 'smtp',
    	'host'	 	 => 'smpt.gmail.com',
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


## Events ##
- mailer:beforeCreateMessage
- mailer:afterCreateMessage
- mailer:beforeSend
- mailer:afterSend
- mailer:beforeAttachFile
- mailer:afterAttachFile
