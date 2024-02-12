<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the "role" property of the User entity.
 */
class UserRoleValidator extends ConstraintValidator
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Validates the "role" property.
     *
     * @param mixed      $value      The value of the "role" property.
     * @param Constraint $constraint The UserRole constraint object.
     *
     * @throws UnexpectedTypeException If the constraint is not an instance of UserRole.
     */
    public function validate($value, Constraint $constraint): void
    {
        // Check if the value is null or empty
        if (null === $value || '' === $value) {
            return;
        }

        // Ensure that the constraint is an instance of UserRole
        if (!$constraint instanceof UserRole) {
            throw new UnexpectedTypeException($constraint, UserRole::class);
        }

        // Get the currently logged-in user
        $user = $this->security->getUser();

        // Ensure that a user is logged in
        if (!$user) {
            throw new \LogicException('UserRoleValidator should only be used when a user is logged in.');
        }

        // Check if the logged-in user has the "ROLE_COMPANY_ADMIN" role and the value is "ROLE_SUPER_ADMIN"
        if ($this->security->isGranted('ROLE_COMPANY_ADMIN') && $value == 'ROLE_SUPER_ADMIN') {
            // Add a validation error to the context
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}