<?php


namespace HalloVerden\DoctrineSqlLoggerBundle\DependencyInjection\Compiler;

use Doctrine\DBAL\Logging\LoggerChain;
use HalloVerden\DoctrineSqlLoggerBundle\DependencyInjection\Configuration;
use HalloVerden\DoctrineSqlLoggerBundle\Loggers\QueryExecutionTimeLogger;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddDoctrineLoggersCompilerPass
 *
 * @package HalloVerden\DoctrineSqlLoggerBundle\DependencyInjection\Compiler
 */
class AddDoctrineLoggersCompilerPass implements CompilerPassInterface {

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container) {
    $config = $this->getConfig($container);

    $configurations = $this->getConnectionConfigurations($container);

    foreach ($configurations as $name => $configuration) {
      $loggers = $this->getLoggers($config, $name);

      if (empty($loggers)) {
        continue;
      }

      // Check if loggers is already set and merge with those.
      if (null !== ($methodsCall = $this->getMethodCall($configuration->getMethodCalls(), 'setSQLLogger'))) {
        $loggers = array_merge($loggers, $methodsCall[1]);
        $configuration->removeMethodCall('setSQLLogger');
      }

      $chainLogger = new Definition(LoggerChain::class, ['$loggers' => $loggers]);
      $chainLoggerId = 'hv_sql_logger.doctrine.dbal.logger.chain.' . $name;
      $container->setDefinition($chainLoggerId, $chainLogger);

      $configuration->addMethodCall('setSQLLogger', [new Reference($chainLoggerId)]);
    }
  }

  /**
   * @param ContainerBuilder $container
   *
   * @return ChildDefinition[]
   */
  private function getConnectionConfigurations(ContainerBuilder $container): array {
    $configurations = [];

    foreach ($container->getDefinitions() as $name => $definition) {
      if (null !== ($connectionName = $this->getConnectionName($name, $definition))) {
        $configurations[$connectionName] = $definition;
      }
    }

    return $configurations;
  }

  /**
   * @param string     $name
   * @param Definition $definition
   *
   * @return string|null
   */
  private function getConnectionName(string $name, Definition $definition): ?string {
    if ($definition instanceof ChildDefinition && $definition->getParent() === 'doctrine.dbal.connection.configuration') {
      \preg_match('/^doctrine\.dbal\.(.+)_connection\.configuration$/', $name, $m);
      return $m[1] ?? null;
    }

    return null;
  }

  /**
   * @param array  $methodCalls
   * @param string $methodName
   *
   * @return array|null
   */
  private function getMethodCall(array $methodCalls, string $methodName): ?array {
    foreach ($methodCalls as $methodCall) {
      if ($methodCall[0] === $methodName) {
        return $methodCall;
      }
    }

    return null;
  }

  /**
   * @param array  $config
   * @param string $connection
   *
   * @return array
   */
  private function getLoggers(array $config, string $connection): array {
    $loggers = [];

    foreach ($config['loggers'] as $service => $logger) {
      if ($logger['enabled'] && in_array($connection, $logger['connections'])) {
        $loggers[] = new Reference($service);
      }
    }

    return $loggers;
  }

  /**
   * @param ContainerBuilder $container
   *
   * @return array
   */
  private function getConfig(ContainerBuilder $container): array {
    $resolvedExtensionConfig = $container->resolveEnvPlaceholders($container->getExtensionConfig(Configuration::NAME), true);
    $config = $this->processConfiguration(new Configuration(), $resolvedExtensionConfig);

    // Enable QueryExecutionTimeLogger per default
    if (!isset($config['loggers'][QueryExecutionTimeLogger::class])) {
      $config['loggers'][QueryExecutionTimeLogger::class] = [
        'enabled' => true,
        'connections' => ['default']
      ];
    }

    return $config;
  }

  /**
   * @param ConfigurationInterface $configuration
   * @param array                  $configs
   *
   * @return array
   */
  private function processConfiguration(ConfigurationInterface $configuration, array $configs): array {
    return (new Processor())->processConfiguration($configuration, $configs);
  }

}
