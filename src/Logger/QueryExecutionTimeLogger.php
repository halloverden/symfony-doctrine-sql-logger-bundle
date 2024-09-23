<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Logger;

use HalloVerden\DoctrineSqlLoggerBundle\Event\QueryExecutionTimeEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class QueryExecutionTimeLogger implements QueryExecutionTimeLoggerInterface {
  private const STOPWATCH_NAME_PREFIX = 'query_execution_time_logger_';

  private readonly Stopwatch $stopwatch;

  /**
   * @var int[]
   */
  private array $thresholds = [];

  /**
   * QueryExecutionTimeLogger constructor.
   */
  public function __construct(
    private readonly LoggerInterface           $logger,
    private readonly ?EventDispatcherInterface $dispatcher = null,
    private readonly int                       $defaultThreshold = 100,
    private readonly bool                      $enableBackTrace = false,
    ?Stopwatch                                 $stopwatch = null,
  ) {
    $this->stopwatch = $stopwatch ?? new Stopwatch();
  }

  public function start(string $sql, array $params = [], array $types = []): QueryExecutionTimeEvent {
    return new QueryExecutionTimeEvent($this->stopwatch->start($this->createStopwatchName()), $this->getThreshold(), $sql, $params, $types);
  }

  public function stop(QueryExecutionTimeEvent $event): void {
    $stopWatchEvent = $event->stopwatchEvent->stop();
    $duration = $stopWatchEvent->getDuration();

    if ($duration < $event->threshold) {
      return;
    }

    $this->dispatcher?->dispatch($event);

    $context = [
      'sql' => $event->sql,
      'params' => $event->params,
      'types' => $event->types,
      'threshold' => $event->threshold,
      'startTime' => $stopWatchEvent->getStartTime(),
      'endTime' => $stopWatchEvent->getEndTime(),
      'executionTime' => $duration,
    ];

    if ($this->enableBackTrace) {
      $backtrace = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

      // skip first since it's always the current method
      array_shift($backtrace);

      $context['backtrace'] = $backtrace;
    }

    $this->logger->warning('Query took {executionTime} ms where threshold is {threshold} ms', $context);
  }

  public function getDefaultThreshold(): int {
    return $this->defaultThreshold;
  }

  /**
   * @inheritDoc
   */
  public function setThresholds(array $thresholds): void {
    $this->thresholds = $thresholds;
  }

  private function getThreshold(): int {
    return \array_shift($this->thresholds) ?? $this->getDefaultThreshold();
  }

  /**
   * @return string
   */
  private function createStopwatchName(): string {
    return self::STOPWATCH_NAME_PREFIX . Uuid::v4();
  }

}
