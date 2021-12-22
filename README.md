# Dot Env


## Install

Via Composer
```bash
$ composer require alireaza/dot-env
```


## Usage

```php
use AliReaza\DotEnv\DotEnv;

$env = new DotEnv('.env');
$env->toArray(); // Array of variables defined in .env
```


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.