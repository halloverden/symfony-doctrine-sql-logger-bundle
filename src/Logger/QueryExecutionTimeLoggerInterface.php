<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Logger;

use HalloVerden\DoctrineSqlLoggerBundle\Event\QueryExecutionTimeEvent;

interface QueryExecutionTimeLoggerInterface {
  public function start(string $sql, array $params = [], array $types = []): QueryExecutionTimeEvent;
  public function stop(QueryExecutionTimeEvent $event): void;
  public function getDefaultThreshold(): int;

  /**
   * @param int[] $thresholds
   *
   * @return void
   */
  public function setThresholds(array $thresholds): void;
}
