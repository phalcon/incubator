# Phalcon\Avatar

Usage examples of the adapters available here:

## Gravatar

[Gravatar's][1] are universal avatars available to all web sites and services.
Users must register their email addresses with Gravatar before their avatars will be usable in your project.

### Getting Started

```php
use Phalcon\Avatar\Gravatar;

$di->setShared('gravatar', function () {
    // Get Gravatar instance
    $gravatar = new Gravatar;
    
    // Setting default image, maximum size and maximum allowed Gravatar rating
    $gravatar->setDefaultImage('retro')
             ->setSize(220)
             ->setRating(Gravatar::RATING_PG);

    return $gravatar;
});
```

### Using

```php
// Get the Gravatar service from DI
$gravatar = $this->getDi()->getShared('gravatar');

// Build the Gravatar URL based on the configuration and provided email address
echo $gravatar->getAvatar('john@doe.com');
```

### Configuration

#### Gravatar size

Gravatar allows avatar images ranging from 0px to 2048px in size.
With using 0px size, images from Gravatar will be returned as 80x80px.

Example:

```php
// Set gravatars size 64x64px
$gravatar->setSize(64);

// Set gravatars size 100x100px
$gravatar->setSize('100');
```

If an invalid size (less than 0, greater than 2048) or a non-integer value is specified,
this method will throw an exception of class `\InvalidArgumentException`.

#### Default Image

Gravatar provides several pre-fabricated default images for use when the email address provided
does not have a gravatar or when the gravatar specified exceeds your maximum allowed content
rating. In addition, you can also set your own default image to be used by providing a valid
URL to the image you wish to use.

There are a few conditions which must be met for default image URL:

- MUST be publicly available (e.g. cannot be on an intranet, on a local development machine, behind HTTP Auth or some other firewall etc). Default images are passed through a security scan to avoid malicious content
- MUST be accessible via HTTP or HTTPS on the standard ports, 80 and 443, respectively
- MUST have a recognizable image extension (jpg, jpeg, gif, png)
- MUST NOT include a query string (if it does, it will be ignored)

Possible values:

- `404` — do not load any image if none is associated with the email, instead return an HTTP 404 (File Not Found) response
- `mm` — (mystery-man) a simple, cartoon-style silhouetted outline of a person (does not vary by email)
- `identicon` — a geometric pattern based on an email
- `monsterid` — a generated 'monster' with different colors, faces, etc
- `wavatar` — generated faces with differing features and backgrounds
- `retro` — awesome generated, 8-bit arcade-style pixelated faces
- `blank` — a transparent PNG image
- boolean false — if using the default gravatar image
- Image URL

Example:

```php
// boolean false for the gravatar default
$gravatar->setDefaultImage(false);

// string specifying a recognized gravatar "default"
$gravatar->setDefaultImage('identicon');

// string with image URL
$gravatar->setDefaultImage('http://example.com/your-default-image.png');
```

If an invalid default image is specified (both an invalid prefab default image and an invalid URL is provided),
this method will throw an exception of class `\InvalidArgumentException`.

#### Using secure connection

Should we use the secure (HTTPS) URL base? If your site is served over HTTPS, you'll likely
want to serve gravatars over HTTPS as well to avoid "mixed content warnings".

Example:

```php
// Enable secure connections:
$gravatar->enableSecureURL();

// Disable secure connections:
$gravatar->disableSecureURL();
```

To check to see if you are using "secure images" mode, call the method `Gravatar::useSecureURL()`,
which will return a boolean value regarding whether or not secure images mode is enabled.

#### Gravatar Rating

Gravatar provides four levels for rating avatars by,
which are named similar to entertainment media ratings scales used in the United States.

Possible values:

- `Gravatar::RATING_G` — suitable for display on all websites with any audience type
- `Gravatar::RATING_PG` — may contain rude gestures, provocatively dressed individuals, the lesser swear words, or mild violence
- `Gravatar::RATING_R` — may contain such things as harsh profanity, intense violence, nudity, or hard drug use
- `Gravatar::RATING_X` — may contain hardcore sexual imagery or extremely disturbing violence

By default, the Gravatar rating is `Gravatar::RATING_G`.

Example:

```php
$gravatar->setRating(Gravatar::RATING_PG);
```

If an invalid maximum rating is specified, this method will throw an exception of class `\InvalidArgumentException`.

#### Force Default

If for some reason you wanted to force the default image to always load, you can enable or disable it.

Example:

```php
// Enable
$gravatar->enableForceDefault();

// Disable
$gravatar->disableForceDefault();
```

To check to see if you are using "Force Default" mode,
call the method `Gravatar::useForceDefault()`, which will return a boolean value.

[1]: http://gravatar.com/
