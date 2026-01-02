<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;
use App\Helpers\ErrorResponse;
use Exception;

/**
 * Generic API exception with ErrorCode support
 * 
 * Use this exception anywhere in your application (controllers, services,
 * repositories, etc.) to return standardized error responses.
 * 
 * This exception automatically renders using the ErrorResponse helper,
 * ensuring consistent error format across the entire application.
 * 
 * Usage:
 *   throw new ApiException(
 *     'Error message',
 *     ErrorCode:: ENUM_ERROR_CODE,
 *     400 // HTTP status code
 *   );
 * 
 * Laravel will automatically catch and render it as a JSON response.
 */
class ApiException extends Exception
{
  protected ErrorCode $errorCode;
  protected int $httpStatusCode;

  /**
   * Create a new ApiException instance
   *
   * @param string $message The error message
   * @param ErrorCode $errorCode The error code enum
   * @param int $httpStatusCode The HTTP status code (default: 400)
   * @param \Throwable|null $previous Previous exception for chaining
   */
  public function __construct(
    string $message,
    ErrorCode $errorCode = ErrorCode::UNKNOWN_ERROR,
    int $httpStatusCode = 400,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 0, $previous);
    $this->errorCode = $errorCode;
    $this->httpStatusCode = $httpStatusCode;
  }

  /**
   * Get the error code
   *
   * @return ErrorCode
   */
  public function getErrorCode(): ErrorCode
  {
    return $this->errorCode;
  }

  /**
   * Get the HTTP status code
   *
   * @return int
   */
  public function getHttpStatusCode(): int
  {
    return $this->httpStatusCode;
  }

  /**
   * Render the exception as an HTTP response using ErrorResponse helper
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function render($request)
  {
    return ErrorResponse::error(
      $this->getMessage(),
      $this->errorCode,
      $this->httpStatusCode
    );
  }
}
