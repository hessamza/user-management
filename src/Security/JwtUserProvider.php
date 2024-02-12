<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Provides user authentication and retrieval for JWT (JSON Web Token) authentication.
 */
class JwtUserProvider implements UserProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Loads a user by their username.
     *
     * @param string $username The username to load the user for.
     *
     * @return UserInterface The user object.
     *
     * @throws AuthenticationException If the user is not found.
     */
    public function loadUserByUsername($username): UserInterface
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['name' => $username]);

        if (!$user) {
            throw new AuthenticationException('User not found.');
        }

        return $user;
    }

    /**
     * Refreshes the user object.
     *
     * @param UserInterface $user The user object to refresh.
     *
     * @return UserInterface The refreshed user object.
     *
     * @throws UnsupportedUserException If the user object is not supported.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Checks if the given class is supported by this user provider.
     *
     * @param string $class The class to check.
     *
     * @return bool Whether the class is supported.
     */
    public function supportsClass($class): bool
    {
        return User::class === $class;
    }

    /**
     * Loads a user by their identifier.
     *
     * @param string $identifier The identifier to load the user for.
     *
     * @return UserInterface The user object.
     *
     * @throws AuthenticationException If the user is not found.
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['name' => $identifier]);

        if (!$user) {
            throw new AuthenticationException('User not found.');
        }

        return $user;
    }
}