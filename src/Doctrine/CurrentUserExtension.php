<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Applies a filter to the collection and item queries based on the current user's company.
 */
final class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Applies the filter to the collection query.
     *
     * @param QueryBuilder                $queryBuilder       The query builder.
     * @param QueryNameGeneratorInterface $queryNameGenerator The query name generator.
     * @param string                      $resourceClass      The resource class.
     * @param Operation|null              $operation          The operation metadata.
     * @param array                       $context            The context.
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Applies the filter to the item query.
     *
     * @param QueryBuilder                $queryBuilder       The query builder.
     * @param QueryNameGeneratorInterface $queryNameGenerator The query name generator.
     * @param string                      $resourceClass      The resource class.
     * @param array                       $identifiers        The item identifiers.
     * @param Operation|null              $operation          The operation metadata.
     * @param array                       $context            The context.
     */
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Adds the WHERE clause to the query builder based on the current user's company.
     *
     * @param QueryBuilder $queryBuilder  The query builder.
     * @param string       $resourceClass The resource class.
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // Check if the resource class is User and the current user has the ROLE_SUPER_ADMIN role
        if (User::class !== $resourceClass
            || $this->security->isGranted('ROLE_SUPER_ADMIN') || null === $user = $this->security->getUser()) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Add the WHERE clause to filter by the current user's company
        $queryBuilder->andWhere(sprintf('%s.company = :current_user', $rootAlias));
        $queryBuilder->setParameter('current_user', $user->getCompany());
    }
}