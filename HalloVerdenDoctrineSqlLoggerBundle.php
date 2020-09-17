<?php


namespace HalloVerden\DoctrineSqlLoggerBundle;


use HalloVerden\DoctrineSqlLoggerBundle\DependencyInjection\Compiler\AddDoctrineLoggersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class HalloVerdenDoctrineSqlLoggerBundle
 *
 * @package HalloVerden\DoctrineSqlLoggerBundle
 */
class HalloVerdenDoctrineSqlLoggerBundle extends Bundle {

  /**
   * @inheritDoc
   */
  public function build(ContainerBuilder $container) {
    parent::build($container);

    $container->addCompilerPass(new AddDoctrineLoggersCompilerPass());
  }

}
