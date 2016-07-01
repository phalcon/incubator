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
