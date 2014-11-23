# Annotation-Based Validation
## Phalcon\Validation\EntityAgent
-
Usage:

```php
class RegisterForm {

	//@Email
	public $email;
	
	//@StringLength(keyName='Your password',min=6,message='{keyName} should has at least {min} characters.')
	public $password;
	
	//@Confirmation(target='password',message='The confirmation is not match.')
	public $confirm;
	
	// No annotation, always passed.
	public $timestamp;
}

$form = new RegisterForm();
$agent = new EntityAgent($di,$form);
$agent->setProperties($_POST);
var_export($agent->doValidate());
/ * *
 * if the $_POST is like {email:"cc@$$.com",password:"123",confirm:"12"}, then the output will like:
 */
 array(
 	'valid'=>false,
 	'message'=>array(
 		'email'=>array(
 			0=>'cc@$$.com is not a valid email address.'
 		),
 		'password'=>array(
 			0=>'Your password should has at least 6 characters.'
 		),
 		'confrim'=>array(
 			0=>'The confirmation is not match.'
 		)
 	)
 )
```

## Phalcon\Validation\Entity
Usage:

```php
class RegisterForm extends Entity {

	//@Email
	public $email;
	
	//@StringLength(keyName='Your password',min=6,message='{keyName} should has at least {min} characters.')
	public $password;
	
	//@Confirmation(target='password',message='The confirmation is not match.')
	public $confirm;
	
	// No annotation, always passed.
	public $timestamp;
}

$form = new RegisterForm($di);
$form->setProperties($_POST);
var_export($form->doValidate());
/ * *
 * if the $_POST is like {email:"cc@$$.com",password:"123",confirm:"12"}, then the output will like:
 */
 array(
 	'valid'=>false,
 	'message'=>array(
 		'email'=>array(
 			0=>'cc@$$.com is not a valid email address.'
 		),
 		'password'=>array(
 			0=>'Your password should has at least 6 characters.'
 		),
 		'confrim'=>array(
 			0=>'The confirmation is not match.'
 		)
 	)
 )
```

## Some Validation Handler (you can see more under Phalcon\Validation\Handler)
* [Phalcon\Validation\Handler\Filter](hu2008yinxiang/incubator/Library/Phalcon/Validation/Handler/Filter) - use <method>filter_var</method> validate the field.
* [Phalcon\Validation\Handler\NotEmpty](hu2008yinxiang/incubator/Library/Phalcon/Validation/Handler/NotEmpty) - validate if the field is empty.
* [Phalcon\Validation\Handler\Between](hu2008yinxiang/incubator/Library/Phalcon/Validation/Handler/Between) - specify a range for the field.
* [Phalcon\Validation\Handler\Number](hu2008yinxiang/incubator/Library/Phalcon/Validation/Handler/Number) - validate if the field is a number.
