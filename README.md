# symfony-doctrine-sql-logger-bundle
Better logging of SQL queries in Doctrine DBAL for Symfony.

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require halloverden/symfony-doctrine-sql-logger-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require halloverden/symfony-doctrine-sql-logger-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    HalloVerden\DoctrineSqlLoggerBundle\HalloVerdenDoctrineSqlLoggerBundle::class => ['all' => true],
];
```

Configuration
============

By default, all you have to do is require this bundle 
and it wil log query execution time on the default connection for all queries that takes more than 100 ms.

```yaml
hallo_verden_doctrine_sql_logger:
    loggers:
        HalloVerden\DoctrineSqlLoggerBundle\Loggers\QueryExecutionTimeLogger:
            enabled: true
            connections:
                - default
```

Set enabled to false to disable QueryExecutionTimeLogger. 
And you can add the connections you want to enable logging on.

You can also add your own loggers by adding them to loggers in the config.

### QueryExecutionTimeLogger
if you want to log backtrace of the query with QueryExecutionTimeLogger add this to services.yaml:

```yaml
HalloVerden\DoctrineSqlLoggerBundle\Loggers\QueryExecutionTimeLogger:
    calls:
        - ['setEnableBacktrace', [true]]
```

if you want to change the execution time threshold for logging add this to services.yaml:

```yaml
HalloVerden\DoctrineSqlLoggerBundle\Loggers\QueryExecutionTimeLogger:
    calls:
        - ['setExecutionTimeThreshold', [150]]
```
