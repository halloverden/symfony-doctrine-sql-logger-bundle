<?php

namespace HalloVerden\DoctrineSqlLoggerBundle;

use HalloVerden\DoctrineSqlLoggerBundle\Doctrine\Middleware\LogQueryExecutionTimeMiddleware;
use HalloVerden\DoctrineSqlLoggerBundle\Logger\QueryExecutionTimeLogger;
use HalloVerden\DoctrineSqlLoggerBundle\Logger\QueryExecutionTimeLoggerInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class HalloVerdenDoctrineSqlLoggerBundle extends AbstractBundle {

  public function configure(DefinitionConfigurator $definition): void {
    $definition->rootNode()
      ->addDefaultsIfNotSet()
      ->children()
        ->arrayNode('loggers')
          ->defaultValue([
            [
              'connection' => 'default',
              'threshold' => 100,
              'paramsLog' => false,
              'backtraceLog' => false,
              'logger' => 'logger'
            ]
          ])
          ->arrayPrototype()
            ->addDefaultsIfNotSet()
            ->children()
              ->scalarNode('connection')->defaultValue('default')->end()
              ->integerNode('threshold')->defaultValue(100)->end()
              ->booleanNode('paramsLog')->defaultValue(false)->end()
              ->booleanNode('backtraceLog')->defaultValue(false)->end()
              ->scalarNode('logger')->defaultValue('logger')->end()
            ->end()
          ->end()
        ->end()
      ->end();
  }

  public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void {
    $alias = $this->getContainerExtension()->getAlias();

    foreach ($config['loggers'] as $loggerConfig) {
      $queryExecutionTimeLoggerId = $alias . '.query_execution_time_logger.' . $loggerConfig['connection'];
      $container->services()
        ->set($queryExecutionTimeLoggerId, QueryExecutionTimeLogger::class)
          ->args([
            service($loggerConfig['logger']),
            service('event_dispatcher')->nullOnInvalid(),
            $loggerConfig['threshold'],
            $loggerConfig['paramsLog'],
            $loggerConfig['backtraceLog'],
            service('debug.stopwatch')->nullOnInvalid()
          ])
        ->alias(QueryExecutionTimeLoggerInterface::class, $queryExecutionTimeLoggerId)
        ->set($alias . '.doctrine_middleware.' . $loggerConfig['connection'], LogQueryExecutionTimeMiddleware::class)
          ->args([service($queryExecutionTimeLoggerId)])
          ->tag('doctrine.middleware', ['connection' => $loggerConfig['connection']])
      ;
    }
  }

}
