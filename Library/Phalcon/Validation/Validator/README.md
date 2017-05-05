# Phalcon\Validation\Validator

## The reCAPTCHA Validator

### Setup

Include Javascript API to your site:

```html
<script async defer src="//www.google.com/recaptcha/api.js"></script>
```

Render reCAPTCHA in your form:

```html
<form>
    <div class="g-recaptcha" data-sitekey="your_site_key"></div>
</form>
```

### Usage

```php
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\ReCaptcha;

$reCaptcha = new Hidden('g-recaptcha-response');

$reCaptcha->setLabel('reCAPTCHA')->addValidators([
    new ReCaptcha([
        'message' => 'The captcha is not valid',
        'secret'  => 'your_site_key',
    ]),
]);

$this->add($reCaptcha);
```


See also:

* [Displaying the widget](https://developers.google.com/recaptcha/docs/display)
* [Verifying the user's response](https://developers.google.com/recaptcha/docs/verify)


## IpValidator

The IpValidator validates a valid ip address.

```php
$data['ip'] = $this->request->getPost('ip');

$validation = new Phalcon\Validation();

$validation->add(
    'ip',
    new MicheleAngioni\PhalconValidators\IpValidator (
        [
            'message' => 'The IP is not valid.'       // Optional
        ]
    )
);

$messages = $validation->validate($data);

if (count($messages)) {
    // Some error occurred, handle messages

}
```

// Validation succeeded without errors

## NumericValidator

The default NumericValidator only allows for numeric (i.e. 0-9) characters.
Minimum and maximum values can be specified.

Optionally, it can support float values, that is allowing a dot (.) character to separate decimals.

Optionally also signed numbers are supported.

```php
$data['number'] = $this->request->getPost('number');

$validation = new Phalcon\Validation();

$validation->add(
    'number',
    new MicheleAngioni\PhalconValidators\NumericValidator (
        [
            'allowFloat' => true,                                           // Optional, default: false
            'allowSign' => true,                                            // Optional, default: false
            'min' => 2,                                                     // Optional
            'min' => 2,                                                     // Optional
            'max' => 50,                                                    // Optional
            'message' => 'Only numeric (0-9,.) characters are allowed.',    // Optional
            'messageMinimum' => 'The value must be at least 2',             // Optional
            'messageMaximum' => 'The value must be lower than 50'           // Optional
        ]
    )
);

$messages = $validation->validate($data);

if (count($messages)) {
    // Some error occurred, handle messages

}

// Validation succeeded without errors
```

## AlphaNumericValidator

The AlphaNumericValidator allows for alphanumeric characters. Optionally, it can allow underscores and white spaces.
Minimum and maximum string lengths can be specified.

```php
$data['text'] = $this->request->getPost('text');

$validation = new Phalcon\Validation();

$validation->add(
    'text',
    new MicheleAngioni\PhalconValidators\AlphaNumericValidator (
        [
            'whiteSpace' => true,                                                       // Optional, default false
            'underscore' => true,                                                       // Optional, default false
            'min' => 6,                                                                 // Optional
            'max' => 30,                                                                // Optional
            'message' => 'Validation failed.',                                          // Optional
            'messageMinimum' => 'The value must contain at least 6 characters.',        // Optional
            'messageMaximum' => 'The value can contain maximum 30 characters.'          // Optional
        ]
    )
);

$messages = $validation->validate($data);

if (count($messages)) {
    // Some error occurred, handle messages

}

// Validation succeeded without errors
```

## AlphaNamesValidator

The AlphaNamesValidator allows for alphabetic, menus, apostrophe, underscore and white space characters.
Optionally, it can allow also numbers (i.t. 0-9).
Minimum and maximum string lengths can be specified.

```php
$data['text'] = $this->request->getPost('text');

$validation = new Phalcon\Validation();

$validation->add(
    'text',
    new MicheleAngioni\PhalconValidators\AlphaNamesValidator (
        [
            'numbers' => true,                                                          // Optional, default false
            'min' => 6,                                                                 // Optional
            'max' => 30,                                                                // Optional
            'message' => 'Validation failed.',                                          // Optional
            'messageMinimum' => 'The value must contain at least 6 characters.',        // Optional
            'messageMaximum' => 'The value can contain maximum 30 characters.'          // Optional
        ]
    )
);

$messages = $validation->validate($data);

if (count($messages)) {
    // Some error occurred, handle messages

}

// Validation succeeded without errors
```

## AlphaCompleteValidator

The AlphaCompleteValidator allows for alphanumeric, underscore, white space, slash, apostrophe, round and square brackets/parentheses and punctuation characters.
Optionally, it can allow also pipes (|), backslashes (\) and Url Characters (equals (=) and hashtags (#)).
Minimum and maximum string lengths can be specified.

```php
$data['text'] = $this->request->getPost('text');

$validation = new Phalcon\Validation();

$validation->add(
    'text',
    new MicheleAngioni\PhalconValidators\AlphaCompleteValidator (
        [
            'allowBackslashes' => true,                                                 // Optional
            'allowPipes' => true,                                                       // Optional
            'allowUrlChars' => true,                                                    // Optional
            'min' => 6,                                                                 // Optional
            'max' => 30,                                                                // Optional
            'message' => 'Validation failed.',                                          // Optional
            'messageMinimum' => 'The value must contain at least 6 characters.',        // Optional
            'messageMaximum' => 'The value can contain maximum 30 characters.'          // Optional
        ]
    )
);

$messages = $validation->validate($data);

if (count($messages)) {
    // Some error occurred, handle messages

}

// Validation succeeded without errors
```
