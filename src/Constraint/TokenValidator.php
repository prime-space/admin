<?php namespace App\Constraint;

use App\Authenticator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TokenValidator extends ConstraintValidator
{
    private $authenticator;

    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Token) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Token');
        }

        if ($this->authenticator->getUser()->token !== $value) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
