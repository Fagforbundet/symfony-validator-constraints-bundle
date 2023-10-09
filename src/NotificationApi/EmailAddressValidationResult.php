<?php

namespace Fagforbundet\ValidatorConstraintsBundle\NotificationApi;

enum EmailAddressValidationResult : string {
  case OK = 'ok';
  case RISKY = 'risky';
  case NOT_OK = 'not_ok';
  case UNKNOWN = 'unknown';

  /**
   * @param string $value
   *
   * @return static
   */
  public static function fromOrUnknown(string $value): self {
    $result = self::tryFrom($value);

    if (null  === $result) {
      $result = self::UNKNOWN;
    }

    return $result;
  }
}
