## Code Challenge

This project is a code-challenge implementation that includes the following features:

| Challenge                                                                                   | 
|---------------------------------------------------------------------------------------------|
| **User Entity**: Create the User entity with the following fields: name, role, and company. |
| **Company Entity**: Create the Company entity with the following field: name                | 
| **API Test**                                                                                | 
| **Dockerized Environment**: Create a dockerized environment for the app.                    | 

To get started with the project, follow the steps below:

``` 
git clone 
cd deployment/management
bash first_setup.sh
```


 Visit `http://localhost:8003` in your browser to access the application.

If you have any questions or need further assistance, feel free to contact me at [hessamvfx@gmail.com](mailto:hessamvfx@gmail.com).


- [USER ENTITY](#user_entity)

1. Create a new file called `User.php` in the appropriate directory (e.g., `src/Entity`).
2. Define the `User` class and annotate it with the `@ORM\Entity` annotation to mark it as an entity for the ORM (Object-Relational Mapping) system.
``` 
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
```
#[ApiResource]: This annotation indicates that the User entity is an API resource and specifies its configuration.

**operations**: This is an array that defines the supported operations for the API resource.

- **GetCollection**: This operation represents the retrieval of a collection of User entities. It has a security requirement that allows access for users with the roles ROLE_SUPER_ADMIN, ROLE_USER, or ROLE_COMPANY_ADMIN. If the security requirement is not met, the securityMessage is returned.
- **Post**: This operation represents the creation of a new User entity. It has a security requirement that allows access for users with the roles ROLE_SUPER_ADMIN or ROLE_COMPANY_ADMIN. It also specifies the normalization and denormalization groups for serialization and deserialization. Additionally, it defines a validation context that includes the validation groups defined in the validationGroups method of the User class.
- **Get**: This operation represents the retrieval of a single User entity. It has a security requirement that allows access for users with the roles ROLE_USER, ROLE_COMPANY_ADMIN, or ROLE_SUPER_ADMIN. If the security requirement is not met, the securityMessage is returned.
- **Delete**: This operation represents the deletion of a User entity. It has a security requirement that allows access only for users with the role ROLE_SUPER_ADMIN. If the security requirement is not met, the securityMessage is returned.
 
This annotation provides a high-level overview of the API resource configuration for the User entity. It specifies the supported operations and their corresponding security requirements

```
#[ORM\Column(type:"string", length: 100)]
#[Groups(['user:read', 'user:write','user:update'])]
#[Assert\NotBlank]
#[Assert\Length(min:3, max:100)]
#[Assert\Regex(
    pattern:"/^(?=.*[A-Z])[A-Za-z ]*$/",
    message:"requires letters and space only, one uppercase letter required"
)]
private ?string $name = null;
```
These annotations are used for mapping the name property to a string column in the database. The column has a maximum length of 100 characters. The property is also annotated with validation rules:
- #[Assert\NotBlank] ensures that the value is not empty or whitespace.
- #[Assert\Length(min:3, max:100)] specifies the minimum and maximum length of the value.
- #[Assert\Regex] defines a regular expression pattern that the value must match. In this case, it requires at least one uppercase letter and allows letters and spaces only.

```
#[ORM\ManyToOne(inversedBy: 'users')]
#[Groups(['user:read', 'user:write','user:update'])]
#[Assert\NotBlank(groups: ['user_company_required'])]
#[Assert\IsNull(groups: ['user_company_should_null'])]
private ?Company $company = null;
```

These annotations are used for mapping the company property to a many-to-one relationship with the Company entity. It represents a foreign key relationship in the database. The property is also annotated with validation rules:
- #[Assert\NotBlank(groups: ['user_company_required'])] ensures that the value is not empty or null. This validation rule is only applied when the validation group user_company_required is active.
- #[Assert\IsNull(groups: ['user_company_should_null'])] ensures that the value is null. This validation rule is only applied when the validation group user_company_should_null is active.

- [COMPANY ENTITY](#company_entity)

1. Create a new file called `company.php` in the appropriate directory (e.g., `src/Entity`).
2. Define the `Company` class and annotate it with the `@ORM\Entity` .

``` 
#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER') or
           is_granted('ROLE_COMPANY_ADMIN')
            or is_granted('ROLE_SUPER_ADMIN')",
            securityMessage: "Access denied."
        ),
        new Post(
            security: "is_granted('ROLE_SUPER_ADMIN') ",
            securityMessage: "Access denied."
        ),
        new Get(
            security: "is_granted('ROLE_USER') or
           is_granted('ROLE_COMPANY_ADMIN') or 
           is_granted('ROLE_SUPER_ADMIN')",
            securityMessage: "Access denied."
        ),
    ]
)]
``` 

This annotation is used to configure the API resource for the Company entity. It specifies the allowed operations and their security requirements.
- **operations**: This is an array of operation annotations that define the allowed operations for the API resource.
- **GetCollection**: This operation annotation represents the GET collection operation, which retrieves a collection of Company entities. It has a security requirement that allows access for users with the roles ROLE_USER, ROLE_COMPANY_ADMIN, or ROLE_SUPER_ADMIN. If the security requirement is not met, the securityMessage is returned.
- **Post**: This operation annotation represents the POST operation, which creates a new Company entity. It has a security requirement that allows access only for users with the ROLE_SUPER_ADMIN role. If the security requirement is not met, the securityMessage is returned.
- **Get**: This operation annotation represents the GET operation, which retrieves a specific Company entity. It has a security requirement that allows access for users with the roles ROLE_USER, ROLE_COMPANY_ADMIN, or ROLE_SUPER_ADMIN. If the security requirement is not met, the securityMessage is returned.



- [UserRoleValidator](#userRoleValidator)

The UserRoleValidator class is a custom validator in Symfony used to validate the role property of the User entity. It extends the ConstraintValidator class, which is the base class for all constraint validators in Symfony.

inside the validate method, i add your custom validation logic specific to the role property. In the provided code snippet, the following validation logic is implemented:
``` 
if ($this->security->isGranted('ROLE_COMPANY_ADMIN') && $value =='ROLE_SUPER_ADMIN') {
    $this->context->buildViolation($constraint->message)
        ->setParameter('{{ value }}', $value)
        ->addViolation();
}
``` 

- The code checks if the currently logged-in user has the role ROLE_COMPANY_ADMIN and if the value of the role property is 'ROLE_SUPER_ADMIN'.
- If the condition is true, it adds a validation error to the validation context using the buildViolation method. The error message is taken from the message property of the UserRole constraint class, and the {{ value }} placeholder is replaced with the actual value of the role property.


- [POSTGRESQL DB](#postgresql_db)
  DATABASE_URL="postgresql://user_management_user:123456@user-management-postgres:5432/user_management_db?serverVersion=16&charset=utf8"
    yaml doctrine: dbal: # ... driver: 'pdo_pgsql'


- [USER PROVIDER](#user_provider)

``` 
    providers:
        jwt_user_provider:
            id: App\Security\JwtUserProvider
```

``` 
class JwtUserProvider implements UserProviderInterface
{

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['name' => $identifier]);

        if (!$user) {
            throw new AuthenticationException('User not found.');
        }

        return $user;
    }
```

To run test :
``` 
docker exec -it user-management-php  ./vendor/bin/phpunit
```
---

The **UserTest** class is a test class for testing the behavior of the User API endpoints. It contains several test methods that test different scenarios related to user access and visibility based on their roles and companies.

Here's a breakdown of the test methods in the **UserTest** class:
1. **testGetUsersOnlyShowsUsersFromSameCompany**: This test method verifies that when a user with the role ROLE_USER sends a GET request to the /api/users endpoint, only the users from the same company are returned in the response. It creates two companies, four users (two from each company), and sends a GET request with one of the users. The test asserts that the response contains only the users from the same company as the requesting user.
2. **testGetUsersExcludesSuperAdminUsers**: This test method checks that super admin users are excluded from the response when a user with the role ROLE_USER sends a GET request to the /api/users endpoint. It creates two companies, two users from the same company, and a super admin user. The test asserts that the response does not contain the super admin user.
3. **testGetUsersOnlyShowsUsersFromSameCompanyForCompanyAdmin**: This test method verifies that when a user with the role ROLE_COMPANY_ADMIN sends a GET request to the /api/users endpoint, only the users from the same company are returned in the response. It creates two companies, two users from the same company, and a user from a different company. The test asserts that the response contains only the users from the same company as the requesting user.
4. **testGetUsersExcludesSuperAdminUsersForCompanyAdmin**: This test method checks that super admin users are excluded from the response when a user with the role ROLE_COMPANY_ADMIN sends a GET request to the /api/users endpoint. It creates two companies, two users from the same company, and a super admin user. The test asserts that the response does not contain the super admin user.
5. **testSuperAdminCanSeeAllUsers** : This test method verifies that a super admin user can see all users, regardless of their company. It creates two companies, three users from different companies, and a super admin user. The test asserts that the response contains all the created users.
6. **testGetUserShowsUserFromSameCompany**: This test method checks that when a user sends a GET request to the /api/users/{id} endpoint with their own user ID, they can see their own user details. It creates two companies and two users from different companies. The test sends a GET request with the ID of one of the users and asserts that the response contains the user's details.

These test methods cover different scenarios related to user access and visibility based on their roles and companies.


The **CompanyTest** class contains several test methods that can be used to test the functionality of the Company API endpoints. Here is a description of each method:

1. **testGetCompaniesShowsAllCompanies()**: This method tests the GET /api/companies endpoint to ensure that it returns all the companies. It creates two companies using the CompanyFactory and a user associated with one of the companies using the UserFactory. It then sends a GET request to the endpoint and asserts that the response status code is 200 (HTTP_OK). It also asserts that the response content is a valid JSON and contains an array of companies with a count of 2.
2. **testGetCompanyShowsCompanyById()**: This method tests the GET /api/companies/{id} endpoint to ensure that it returns a specific company by its ID. It creates a company using the CompanyFactory and a user associated with the company using the UserFactory. It then sends a GET request to the endpoint with the company's ID and asserts that the response status code is 200 (HTTP_OK). It also asserts that the response content is a valid JSON and contains the expected company ID.
3. **testGetCompaniesShowsAllCompaniesForCompanyAndSuperAdmin()**: This method tests the GET /api/companies endpoint for both a company user and a super admin user. It creates two companies using the CompanyFactory, a company user associated with one of the companies using the UserFactory, and a super admin user using the UserFactory. It then sends GET requests to the endpoint with both users and asserts that the response status codes are 200 (HTTP_OK). It also asserts that the response contents are valid JSONs and contain arrays of companies with a count of 2.
4. **testGetCompanyShowsCompanyByIdForCompanyAndSuperAdmin()**: This method tests the GET /api/companies/{id} endpoint for both a company user and a super admin user. It creates a company using the CompanyFactory, a company user associated with the company using the UserFactory, and a super admin user using the UserFactory. It then sends GET requests to the endpoint with both users and asserts that the response status codes are 200 (HTTP_OK). It also asserts that the response contents are valid JSONs and contain the expected company ID.
5. **testCreateCompanyNotAllowedForUserAndCompany()**: This method tests the POST /api/companies endpoint to ensure that creating a company is not allowed for a user associated with a company. It creates a company using the CompanyFactory, a user associated with the company using the UserFactory, and a company data array. It then sends a POST request to the endpoint with the user and the company data and asserts that the response status code is 403 (HTTP_FORBIDDEN).
6. **testCreateCompanyAvailableForSuperAdminOnly()**: This method tests the POST /api/companies endpoint to ensure that creating a company is only available for a super admin user. It creates a super admin user using the UserFactory and a company data array. It then sends a POST request to the endpoint with the super admin user and the company data and asserts that the response status code is 201 (HTTP_CREATED). It also asserts that the response content is a valid JSON and contains the expected company name. Additionally, it validates the requirements for the name field, including its length and uniqueness in the database.

These test methods cover various scenarios and ensure that the Company API endpoints are functioning correctly.