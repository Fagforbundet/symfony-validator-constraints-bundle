<?php

namespace Fagforbundet\ValidatorConstraintsBundle\Validator\Constraint;

use Fagforbundet\ValidatorConstraintsBundle\NotificationApi\EmailAddressValidationMethod;
use Fagforbundet\ValidatorConstraintsBundle\NotificationApi\EmailAddressValidationResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NotificationApiEmailAddressValidator extends ConstraintValidator {
  private const VALIDATE_EMAIL_PATH = '/v2/validate/email-address';

  /**
   * NotificationApiEmailAddressValidator constructor.
   */
  public function __construct(
    private readonly HttpClientInterface $client
  ) {
  }

  /**
   * @inheritDoc
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof NotificationApiEmailAddress) {
      throw new UnexpectedTypeException($constraint, NotificationApiEmailAddress::class);
    }

    if (null === $value || '' === $value) {
      return;
    }

    if (!\is_scalar($value) && !$value instanceof \Stringable) {
      throw new UnexpectedValueException($value, 'string');
    }

    $value = (string) $value;

    try {
      $response = $this->client->request(Request::METHOD_POST, self::VALIDATE_EMAIL_PATH, [
        'json' => [
          'emailAddress' => $value,
          'validationMethods' => $constraint->validationMethods ? \array_map(fn(EmailAddressValidationMethod $method) => $method->value, $constraint->validationMethods) : null
        ]
      ]);
      $content = $response->toArray();
    } catch (ExceptionInterface $e) {
      $this->context->buildViolation($constraint->messageRequestFailed)
        ->setParameter('{{ error }}', $this->formatValue($e->getMessage()))
        ->setCode(NotificationApiEmailAddress::REQUEST_FAILED_ERROR)
        ->addViolation();
      return;
    }

    $result = EmailAddressValidationResult::fromOrUnknown($content['result']);

    if ($result === EmailAddressValidationResult::OK) {
      return;
    }

    if ($constraint->allowRisky && $result === EmailAddressValidationResult::RISKY) {
      return;
    }

    $this->context->buildViolation($constraint->message)
      ->setParameter('{{ value }}', $this->formatValue($value))
      ->setParameter('{{ result }}', $this->formatValue($result->value))
      ->setCode(NotificationApiEmailAddress::INVALID_EMAIL_ERROR)
      ->addViolation();

    foreach ($content['detailedResults'] as $method => $detailedResult) {
      $result = EmailAddressValidationResult::fromOrUnknown($detailedResult['result']);

      if ($result === EmailAddressValidationResult::OK) {
        continue;
      }

      if ($constraint->allowRisky && $result === EmailAddressValidationResult::RISKY) {
        continue;
      }

      $method = EmailAddressValidationMethod::fromOrUnknown($method);
      $this->context->buildViolation($constraint->getErrorMessageByMethod($method))
        ->setParameter('{{ value }}', $this->formatValue($value))
        ->setParameter('{{ result }}', $this->formatValue($result->value))
        ->setParameter('{{ error }}', $this->formatValue($detailedResult['error'] ?? null))
        ->setParameter('{{ warnings }}', $this->formatValues($detailedResult['warnings']))
        ->setCode(NotificationApiEmailAddress::getErrorCodeByMethod($method))
        ->addViolation();
    }
  }

}
