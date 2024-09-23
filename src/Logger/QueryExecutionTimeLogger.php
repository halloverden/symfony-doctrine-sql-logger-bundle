<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Logger;

use HalloVerden\DoctrineSqlLoggerBundle\Context\QueryExecutionTimeContext;
use HalloVerden\DoctrineSqlLoggerBundle\Event\QueryExecutionTimeEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class QueryExecutionTimeLogger implements QueryExecutionTimeLoggerInterface {
  private const STOPWATCH_NAME_PREFIX = 'query_execution_time_logger_';

  private readonly Stopwatch $stopwatch;

  /**
   * @var QueryExecutionTimeContext[]
   */
  private array $contexts = [];

  /**
   * QueryExecutionTimeLogger constructor.
   */
  public function __construct(
    private readonly LoggerInterface           $logger,
    private readonly ?EventDispatcherInterface $dispatcher = null,
    private readonly int                       $defaultThreshold = 100,
    private readonly bool                      $enableParamsLog = false,
    private readonly bool                      $enableBacktraceLog = false,
    ?Stopwatch                                 $stopwatch = null,
  ) {
    $this->stopwatch = $stopwatch ?? new Stopwatch();
  }

  public function start(string $sql, array $params = [], array $types = []): QueryExecutionTimeEvent {
    $uuid = Uuid::v4();
    return new QueryExecutionTimeEvent($this->stopwatch->start(self::STOPWATCH_NAME_PREFIX . $uuid), $this->getContext(), $sql, $uuid, $params, $types);
  }

  public function stop(QueryExecutionTimeEvent $event): void {
    $stopWatchEvent = $event->stopwatchEvent->stop();
    $duration = $stopWatchEvent->getDuration();

    if ($duration <= $event->context->threshold) {
      return;
    }

    $this->dispatcher?->dispatch($event);

    $context = [
      'sql' => $event->sql,
      'executionTime' => $duration,
      'eventUuid' => $event->uuid,
      'eventContext' => $event->context->toArray(),
      'stopwatchEvent' => $stopWatchEvent->getName(),
      'startTime' => $stopWatchEvent->getStartTime(),
      'endTime' => $stopWatchEvent->getEndTime(),
    ];

    if ($this->enableParamsLog) {
      $context['params'] = $event->params;
      $context['types'] = $event->types;
    }

    if ($this->enableBacktraceLog) {
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
  public function addContext(QueryExecutionTimeContext ...$contexts): void {
    \array_push($this->contexts, ...$contexts);
  }

  /**
   * @return QueryExecutionTimeContext
   */
  private function getContext(): QueryExecutionTimeContext {
    return \array_shift($this->contexts) ?? new QueryExecutionTimeContext($this->getDefaultThreshold());
  }

}
