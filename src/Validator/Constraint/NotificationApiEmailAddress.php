<?php

namespace Fagforbundet\ValidatorConstraintsBundle\Validator\Constraint;

use Fagforbundet\ValidatorConstraintsBundle\NotificationApi\EmailAddressValidationMethod;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class NotificationApiEmailAddress extends Constraint {
  public const INVALID_SYNTAX_ERROR = 'cc705b96-bf86-4ef2-aeeb-ef058020135c';
  public const INVALID_DNS_ERROR = '4496c76c-1184-4f05-a047-03ba6ca0863b';
  public const INVALID_SEVEN_BIT_ASCII_LOCAL_PART_ERROR = '2611e5a4-76cd-45b5-ac1d-62a6c1f658ad';
  public const INVALID_EMAIL_ERROR = '9c940fa1-7af1-40d4-ae4a-cef82fb963cf';
  public const REQUEST_FAILED_ERROR = 'd143e2fe-a4c7-448c-b08f-cd9d742a04dc';
  public const UNKNOWN_ERROR = 'e7abc833-1fc0-4617-8605-8ebbe0b8f296';

  protected const ERROR_NAMES = [
    self::INVALID_SYNTAX_ERROR => 'INVALID_SYNTAX_ERROR',
    self::INVALID_DNS_ERROR => 'INVALID_DNS_ERROR',
    self::INVALID_SEVEN_BIT_ASCII_LOCAL_PART_ERROR => 'INVALID_SEVEN_BIT_ASCII_LOCAL_PART_ERROR',
    self::INVALID_EMAIL_ERROR => 'INVALID_EMAIL_ERROR',
    self::REQUEST_FAILED_ERROR => 'REQUEST_FAILED_ERROR',
    self::UNKNOWN_ERROR => 'UNKNOWN_ERROR',
  ];

  public string $message = 'This value is not a valid email address.';
  public string $messageInvalidSyntax = 'Invalid email address syntax: {{ error }}';
  public string $messageInvalidDns = 'Email address does not have a valid DNS: {{ error }}';
  public string $messageInvalidSevenBitAscii = 'The local part of this email address contains invalid characters: {{ error }}';
  public string $messageRequestFailed = 'Request to notification API failed: {{ error }}';
  public string $messageUnknown = 'Unknown error: {{ error }}';

  /**
   * @var EmailAddressValidationMethod[]|null
   */
  public ?array $validationMethods = null;

  public bool $allowRisky = false;

  /**
   * NotificationApiEmailAddress constructor.
   */
  public function __construct(
    ?array $options = null,
    ?string $message = null,
    ?string $messageInvalidSyntax = null,
    ?string $messageInvalidDns = null,
    ?string $messageInvalidSevenBitAscii = null,
    ?string $messageRequestFailed = null,
    ?string $messageUnknown = null,
    ?array $validationMethods = null,
    ?bool $allowRisky = null,
    ?array $groups = null,
    mixed $payload = null
  ) {
    parent::__construct($options, $groups, $payload);

    $this->message = $message ?? $this->message;
    $this->messageInvalidSyntax = $messageInvalidSyntax ?? $this->messageInvalidSyntax;
    $this->messageInvalidDns = $messageInvalidDns ?? $this->messageInvalidDns;
    $this->messageInvalidSevenBitAscii = $messageInvalidSevenBitAscii ?? $this->messageInvalidSevenBitAscii;
    $this->messageRequestFailed = $messageRequestFailed ?? $this->messageRequestFailed;
    $this->messageUnknown = $messageUnknown ?? $this->messageUnknown;
    $this->validationMethods = $validationMethods ?? $this->validationMethods;
    $this->allowRisky = $allowRisky ?? $this->allowRisky;
  }

  /**
   * @param EmailAddressValidationMethod $method
   *
   * @return string
   */
  public static function getErrorCodeByMethod(EmailAddressValidationMethod $method): string {
    return match($method) {
      EmailAddressValidationMethod::SYNTAX => self::INVALID_SYNTAX_ERROR,
      EmailAddressValidationMethod::DNS => self::INVALID_DNS_ERROR,
      EmailAddressValidationMethod::SevenBitAsciiLocalPart => self::INVALID_SEVEN_BIT_ASCII_LOCAL_PART_ERROR,
      EmailAddressValidationMethod::UNKNOWN => self::UNKNOWN_ERROR,
    };
  }

  /**
   * @param EmailAddressValidationMethod $method
   *
   * @return string
   */
  public function getErrorMessageByMethod(EmailAddressValidationMethod $method) : string {
    return match ($method) {
      EmailAddressValidationMethod::SYNTAX => $this->messageInvalidSyntax,
      EmailAddressValidationMethod::DNS => $this->messageInvalidDns,
      EmailAddressValidationMethod::SevenBitAsciiLocalPart => $this->messageInvalidSevenBitAscii,
      EmailAddressValidationMethod::UNKNOWN => $this->messageUnknown,
    };
  }

}
