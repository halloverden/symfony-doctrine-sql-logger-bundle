<?php


namespace HalloVerden\DoctrineSqlLoggerBundle\Loggers;


use Doctrine\DBAL\Logging\SQLLogger;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Logger\DbalLogger;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class QueryExecutionTimeLogger
 *
 * @package HalloVerden\DoctrineSqlLoggerBundle\Loggers
 */
class QueryExecutionTimeLogger implements SQLLogger {
  const STOPWATCH_NAME = 'doctrine_query_execution_time';
  const MAX_STRING_LENGTH = 32;
  const BINARY_DATA_VALUE = '(binary value)';
  const DEFAULT_MAX_EXECUTION_TIME_THRESHOLD = 100;

  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var Stopwatch
   */
  private $stopWatch;

  /**
   * @var bool
   */
  private $enableBacktrace;

  /**
   * @var int
   */
  private $executionTimeThreshold;

  /**
   * @var array|null
   */
  private $currentQuery;

  /**
   * QueryExecutionTimeLogger constructor.
   *
   * @param LoggerInterface $executionTimeLogger
   * @param bool            $enableBacktrace
   * @param int             $executionTimeThreshold
   */
  public function __construct(LoggerInterface $executionTimeLogger, bool $enableBacktrace = false, int $executionTimeThreshold = self::DEFAULT_MAX_EXECUTION_TIME_THRESHOLD) {
    $this->logger = $executionTimeLogger;
    $this->stopWatch = new Stopwatch();
    $this->enableBacktrace = $enableBacktrace;
    $this->executionTimeThreshold = $executionTimeThreshold;
  }

  /**
   * @param bool $enableBacktrace
   */
  public function setEnableBacktrace(bool $enableBacktrace): void {
    $this->enableBacktrace = $enableBacktrace;
  }

  /**
   * @param int $executionTimeThreshold
   */
  public function setExecutionTimeThreshold(int $executionTimeThreshold): void {
    $this->executionTimeThreshold = $executionTimeThreshold;
  }

  /**
   * @inheritDoc
   */
  public function startQuery($sql, ?array $params = null, ?array $types = null) {
    $this->stopWatch->reset();
    $this->stopWatch->start(self::STOPWATCH_NAME);

    $this->currentQuery = ['sql' => $sql, 'params' => null === $params ? [] : $this->normalizeParams($params)];
  }

  /**
   * @inheritDoc
   */
  public function stopQuery() {
    if (!$this->currentQuery) {
      return;
    }

    $event = $this->stopWatch->stop(self::STOPWATCH_NAME);
    $this->stopWatch->reset();
    $duration = $event->getDuration();

    if ($duration < $this->executionTimeThreshold) {
      return;
    }

    $context = $this->currentQuery + ['executionTime' => $duration];

    if ($this->enableBacktrace) {
      $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
      $context += ['backtrace' => $backtrace];
    }

    $this->logger->info(\sprintf('Query took %d ms', $duration), $context);
  }

  /**
   * @see DbalLogger::normalizeParams()
   *
   * @param array $params
   *
   * @return array
   */
  protected function normalizeParams(array $params): array {
    foreach ($params as $index => $param) {
      // normalize recursively
      if (\is_array($param)) {
        $params[$index] = $this->normalizeParams($param);
        continue;
      }

      if (!\is_string($params[$index])) {
        continue;
      }

      // non utf-8 strings break json encoding
      if (!preg_match('//u', $params[$index])) {
        $params[$index] = self::BINARY_DATA_VALUE;
        continue;
      }

      // detect if the string is too long, and must be shortened
      if (self::MAX_STRING_LENGTH < mb_strlen($params[$index], 'UTF-8')) {
        $params[$index] = mb_substr($params[$index], 0, self::MAX_STRING_LENGTH - 6, 'UTF-8').' [...]';
        continue;
      }
    }

    return $params;
  }

}
