<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\UserRepository;
use App\Validator\UserRole;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_SUPER_ADMIN') or (is_granted('ROLE_USER') or
            (is_granted('ROLE_COMPANY_ADMIN') ))",
            securityMessage: "Access denied."
        ),
        new Post(
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_SUPER_ADMIN') or
           is_granted('ROLE_COMPANY_ADMIN')",
            securityMessage: "Access denied.",
            validationContext: ['groups'=>[User::class, 'validationGroups']]
        ),
        new Get(
            security: "is_granted('ROLE_USER') or
           is_granted('ROLE_COMPANY_ADMIN')  or is_granted('ROLE_SUPER_ADMIN')",
            securityMessage: "Access denied."
        ),
        new Delete(
            security: "is_granted('ROLE_SUPER_ADMIN')",
            securityMessage: "Access denied."
        ),
        ]
)]

class User implements UserInterface
{
    public const ROLES = ["ROLE_USER", "ROLE_COMPANY_ADMIN", "ROLE_SUPER_ADMIN"];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"string", length: 100)]
    #[Groups(['user:read', 'user:write','user:update'])]
    #[Assert\NotBlank]
    #[Assert\Length(min:3, max:100)]
    #[Assert\Regex(
        pattern:"/^(?=.*[A-Z])[A-Za-z ]*$/",
        message:"requires letters and space only, one uppercase letter required"
    )]
    private ?string $name = null;

    #[ORM\Column(type:"string", length: 100)]
    #[Groups(['user:read', 'user:write','user:update'])]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Choice(choices: User::ROLES, message: 'Choose a valid roles.')]
    #[UserRole]
    private ?string $role = null;



    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(['user:read', 'user:write','user:update'])]
    #[Assert\NotBlank(groups: ['user_company_required'])]
    #[Assert\IsNull(groups: ['user_company_should_null'])]
    private ?Company $company = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {

        $this->role = $role;

        return $this;
    }


    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->name;
    }

    public function getRoles(): array
    {
        return [$this->role];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->name;
    }
    public function getSalt(): ?string
    {
        return null;
    }

    public static function validationGroups(self $user): array
    {

        $validationGroups = ['Default'];

        if ($user->role === 'ROLE_USER' || $user->role === 'ROLE_COMPANY_ADMIN') {
            $validationGroups[] = 'user_company_required';
        }

        if ($user->role === 'ROLE_SUPER_ADMIN') {
            $validationGroups[] = 'user_company_should_null';
        }

        return $validationGroups;
    }
}
