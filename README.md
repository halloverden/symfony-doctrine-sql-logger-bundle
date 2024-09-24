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
        -   connection: default
            threshold: 100
            paramsLog: false
            backtraceLog: false
            logger: logger
```

### Change context for specific query
Before executing a query you can change the context by injecting `QueryExecutionTimeLoggerInterface` and add a context:

```php
readonly class MyRepository {
    public function __construct(
        private QueryExecutionTimeLoggerInterface $queryExecutionTimeLogger
    ) {
    }
    
    public function executeQuery(): User {
        $this->queryExecutionTimeLogger->addContext(new QueryExecutionTimeContext(threshold: 500));
        // ... Execute query
    }
}
```

### QueryExecutionTimeEvent
When a query exceeds the threshold a `QueryExecutionTimeEvent` is dispatched.

Keep in mind that any query executed within this event will not be timed and logged.
