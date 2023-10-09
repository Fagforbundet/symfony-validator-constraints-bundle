<?php

namespace Fagforbundet\ValidatorConstraintsBundle\NotificationApi;

enum EmailAddressValidationMethod : string {
  case SYNTAX = 'syntax';
  case DNS = 'dns';
  case SevenBitAsciiLocalPart = '7BitAsciiLocalPart';
  case UNKNOWN = 'unknown';

  /**
   * @param string $value
   *
   * @return self
   */
  public static function fromOrUnknown(string $value): self {
    $result = self::tryFrom($value);

    if (null  === $result) {
      $result = self::UNKNOWN;
    }

    return $result;
  }
}
