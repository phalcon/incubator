# Phalcon\Validation\Validator

## reCAPTCHA

### Include Javascript API to your site

```html
<script async defer src="//www.google.com/recaptcha/api.js"></script>
```

### Render reCAPTCHA in your form

```html
<form>
    <div class="g-recaptcha" data-sitekey="your_site_key"></div>
</form>
```
[Displaying the widget](https://developers.google.com/recaptcha/docs/display)

### Verify your reCAPTCHA on form validation

```php
$reCaptcha = new \Phalcon\Forms\Element\Hidden('g-recaptcha-response');
$reCaptcha->setLabel('reCAPTCHA')
    ->addValidators(array(
        new \Phalcon\Validation\Validator\ReCaptcha(array(
            'message' => 'The captcha is not valid',
            'secret'  => 'your_site_key',
        )),
    ));
$this->add($reCaptcha);
```

[Verifying the user's response](https://developers.google.com/recaptcha/docs/verify)