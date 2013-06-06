Phalcon\Config\Adapter
======================

Usage examples of the adapters available here:

Yaml
----
Reads yaml markup files and convert it to Phalcon\Config objects. Given the next configuration file:

```

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
define('CONFKEY', 'secret');

$config = new Phalcon\Config\Adapter\Yaml('path/config.yml', array(
	'!decrypt' => function($value) {
		return (new Phalcon\Crypt)->setCipher('blowfish')->decryptBase64($value, CONFKEY);
	},
	'!approot' => function($value) {
		return APPROOT . $value;
	}
));

echo $config->phalcon->controllersDir;
echo $config->database->username;
echo $config->database->password;

```

Json
----
Reads Json markup files and converts it to Phalcon\Config objects. For the following configuration file:

```

{
    "database": {
        "adapter":  "Mysql",
        "host":     "localhost",
        "username": "scott",
        "password": "dbpassword",
        "dbname":   "test_db"
    },

    "phalcon": {
        "controllersDir":   "../app/controllers/",
        "modelsDir":        "../app/models/",
        "viewsDir":         "../app/views/"
    }
}

```

You can read it as follows:

```php

$config = new Phalcon\Config\Adapter\Json('path/config.json');

echo $config->phalcon->controllersDir;
echo $config->database->username;
echo $config->database->password;

```
