<?php


namespace HalloVerden\DoctrineSqlLoggerBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
  const NAME = 'hallo_verden_doctrine_sql_logger';
  const DEFAULT_MAX_EXECUTION_TIME_MS = 100;

  /**
   * @inheritDoc
   */
  public function getConfigTreeBuilder(): TreeBuilder {
    $treeBuilder = new TreeBuilder(self::NAME);

    $treeBuilder->getRootNode()
      ->addDefaultsIfNotSet()
      ->children()
        ->arrayNode('loggers')
          ->arrayPrototype()
            ->children()
              ->booleanNode('enabled')->defaultTrue()->end()
              ->arrayNode('connections')
                ->defaultValue(['default'])
                ->scalarPrototype()->end()
              ->end()
            ->end()
          ->end()
        ->end()
      ->end()
    ;

    return $treeBuilder;
  }

}
