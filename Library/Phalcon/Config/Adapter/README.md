# Phalcon\Config\Adapter

Usage examples of the adapters available here:

## Yaml

Reads yaml markup files and convert it to `Phalcon\Config` objects. Given the next configuration file:

```yaml

database:
  adapter:  Mysql
  host:     localhost
  username: scott
  password: !decrypt 3YxGGzyQ5xrsvhQAemqZlw==
  dbname:   test_db

phalcon:
  controllersDir: !approot  /app/controllers/
  modelsDir:      !approot  /app/models/
  viewsDir:       !approot  /app/views/

```

You can read it as follows:

```php

define('APPROOT', dirname(__DIR__));

$config = new Phalcon\Config\Adapter\ExtendedYaml('path/config.yml', [
	'!decrypt' => function($value) {
		return (new Phalcon\Crypt)->setCipher('blowfish')->decryptBase64($value, getenv('CONFKEY'));
	},
	'!approot' => function($value) {
		return APPROOT . $value;
	}
]);

echo $config->phalcon->controllersDir;
echo $config->database->username;
echo $config->database->password;

```
