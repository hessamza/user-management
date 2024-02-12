<?php

namespace App\Tests\API;

use App\DataFixtures\Factory\CompanyFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Repository\UserRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserTest extends TestCase
{
    use Factories;
    use ResetDatabase;

    public function testGetUsersOnlyShowsUsersFromSameCompany(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        $user1 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user3 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company2])->create();
        $user4 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company2])->create();

        // Act
        $response = $this->sendGetRequest('/api/users', $user1->object());

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data['hydra:member']);
        $this->assertContains($user1->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertContains($user2->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertNotContains($user3->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertNotContains($user4->getId(), array_column($data['hydra:member'], 'id'));
    }


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetUsersExcludesSuperAdminUsers(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        $user1 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $superAdmin = UserFactory::new(['role' => 'ROLE_SUPER_ADMIN'])->create();

        // Act
        $response = $this->sendGetRequest('/api/users', $user1->object());


        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data['hydra:member']);
        $this->assertContains($user1->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertContains($user2->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertNotContains($superAdmin->getId(), array_column($data['hydra:member'], 'id'));
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetUsersOnlyShowsUsersFromSameCompanyForCompanyAdmin(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        $user1 = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user3 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company2])->create();

        // Act
        $response = $this->sendGetRequest('/api/users', $user1->object());


        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data['hydra:member']);
        $this->assertContains($user1->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertContains($user2->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertNotContains($user3->getId(), array_column($data['hydra:member'], 'id'));
    }


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetUsersExcludesSuperAdminUsersForCompanyAdmin(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        $user1 = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $superAdmin = UserFactory::new(['role' => 'ROLE_SUPER_ADMIN'])->create();

        // Act
        $response = $this->sendGetRequest('/api/users', $user1->object());


        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data['hydra:member']);
        $this->assertContains($user1->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertContains($user2->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertNotContains($superAdmin->getId(), array_column($data['hydra:member'], 'id'));
    }


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testSuperAdminCanSeeAllUsers(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        $user1 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN', 'company' => $company1])->create();
        $user3 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company2])->create();
        $superAdmin = UserFactory::new(['role' => 'ROLE_SUPER_ADMIN'])->create();

        // Act
        $response = $this->sendGetRequest('/api/users', $superAdmin->object());


        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(4, $data['hydra:member']);
        $this->assertContains($user1->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertContains($user2->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertContains($user3->getId(), array_column($data['hydra:member'], 'id'));
        $this->assertContains($superAdmin->getId(), array_column($data['hydra:member'], 'id'));
    }


    public function testGetUserShowsUserFromSameCompany(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        $user1 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company2])->create();


        // Act
        $response = $this->sendGetRequest('/api/users/' . $user1->getId(), $user1->object());


        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($user1->getId(), $data['id']);
    }

    public function testGetUserExcludesUserFromDifferentCompany(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        $user1 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company2])->create();


        $response = $this->sendGetRequest('/api/users/' . $user1->getId(), $user2->object());
        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_NOT_FOUND);
    }

    public function testSuperAdminCanSeeSingleUser(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        $user1 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company2])->create();
        $superAdmin = UserFactory::new(['role' => 'ROLE_SUPER_ADMIN'])->create();


        $response = $this->sendGetRequest('/api/users/' . $user1->getId(), $superAdmin->object());

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($user1->getId(), $data['id']);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetUserShowsUserFromSameCompanyForCompanyAdmin(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        $user1 = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user3 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company2])->create();


        $response = $this->sendGetRequest('/api/users/' . $user2->getId(), $user1->object());

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($user2->getId(), $data['id']);
    }

    public function testGetUserExcludesUserFromDifferentCompanyForCompanyAdmin(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        $user1 = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user3 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company2])->create();



        $response = $this->sendGetRequest('/api/users/' . $user3->getId(), $user1->object());

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_NOT_FOUND);
    }


    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateUser(): void
    {
        // Arrange
        $company = CompanyFactory::new()->create();
        $superAdmin = UserFactory::new(['role' => 'ROLE_SUPER_ADMIN'])->create();
        $companyAdmin = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN'])->create();
        $user = UserFactory::new(['role' => 'ROLE_USER'])->create();

        $userData = [
            'name' => 'dddddd D ',
            'role' => 'ROLE_USER',
            'company' => "/api/companies/". $company->getId(),
        ];

        // Act

        $responseSuperAdmin = $this->sendPostRequest('/api/users', $userData, $superAdmin->object());
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_CREATED, $responseSuperAdmin->getStatusCode());

        $responseCompanyAdmin = $this->sendPostRequest('/api/users', $userData, $companyAdmin->object());
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_CREATED, $responseCompanyAdmin->getStatusCode());


        $responseUser = $this->sendPostRequest('/api/users', $userData, $user->object());
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_FORBIDDEN, $responseUser->getStatusCode());
    }


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface|DecodingExceptionInterface
     */
    public function testCreateUserValidationNameRequired(): void
    {
        // Arrange
        $company = CompanyFactory::new()->create();
        $companyAdmin = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN'])->create();

        // Act
        $this->sendPostRequest('/api/users', [
            'role' => 'ROLE_USER',
            'company' => "/api/companies/". $company->getId(),
        ], $companyAdmin->object());


        self::assertResponseStatusCodeSame(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        self::assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');
        self::assertJsonContains([
            'title' => 'An error occurred',
            'detail' => 'name: This value should not be blank.',
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function testCreateUserValidationNameLength(): void
    {
        // Arrange
        $company = CompanyFactory::new()->create();
        $companyAdmin = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN'])->create();

        // Act
         $this->sendPostRequest('/api/users', [
            'name' => 'Jo',
            'role' => 'ROLE_USER',
            'company' => "/api/companies/". $company->getId(),
         ], $companyAdmin->object());

        // Assert
        self::assertResponseStatusCodeSame(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        self::assertJsonContains([
            'title' => 'An error occurred',
            'violations' => [
                [
                    'propertyPath' => 'name',
                    'message' => 'This value is too short. It should have 3 characters or more.',
                ],
            ],
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function testCreateUserValidationNameFormat(): void
    {
        // Arrange
        $company = CompanyFactory::new()->create();
        $companyAdmin = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN'])->create();

        // Act
        $this->sendPostRequest('/api/users', [
            'name' => 'john doe',
            'role' => 'ROLE_USER',
            'company' => "/api/companies/". $company->getId(),
        ], $companyAdmin->object());

        // Assert
        self::assertResponseStatusCodeSame(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        self::assertJsonContains([
            'title' => 'An error occurred',
            'violations' => [
                [
                    'propertyPath' => 'name',
                    'message' => 'requires letters and space only, one uppercase letter required',
                ],
            ],
        ]);
    }

//====================================================================
    //Check  Role Validations

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function testRoleIsRequired(): void
    {
        // Arrange
        $user = UserFactory::new()->create();
        $company = CompanyFactory::new()->create();
        $companyAdmin = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN'])->create();

        // Act
        $this->sendPostRequest('/api/users', [
            'name' => 'john Doe',
            'company' => "/api/companies/". $company->getId(),
        ], $companyAdmin->object());

        // Assert
        self::assertResponseStatusCodeSame(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        self::assertJsonContains([
            'title' => 'An error occurred',
            'violations' => [
                [
                    'propertyPath' => 'role',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testRoleMustBeString(): void
    {
        // Arrange
        $user = UserFactory::new()->create();
        $company = CompanyFactory::new()->create();
        $companyAdmin = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN'])->create();

        // Act
        $this->sendPostRequest('/api/users', [
            'name' => 'john Doe',
            'company' => "/api/companies/". $company->getId(),
            'role' => 123, // role field is not a string
        ], $companyAdmin->object());

        // Assert
        self::assertResponseStatusCodeSame(HttpResponse::HTTP_BAD_REQUEST);
        self::assertJsonContains([
            'title' => 'An error occurred',
            'detail' =>"The type of the \"role\" attribute must be \"string\", \"integer\" given."
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testRoleMustBeValidChoice(): void
    {
        // Arrange
        $user = UserFactory::new()->create();
        $company = CompanyFactory::new()->create();
        $companyAdmin = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN'])->create();


        // Act
        $this->sendPostRequest('/api/users', [
            'name' => 'john Doe',
            'company' => "/api/companies/". $company->getId(),
            'role' => 'ROLE_INVALID', // role field is not a valid choice
        ], $companyAdmin->object());


        // Assert
        self::assertResponseStatusCodeSame(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        self::assertJsonContains([
            'title' => 'An error occurred',
            'violations' => [
                [
                    'propertyPath' => 'role',
                    'message' => 'Choose a valid roles.',
                ],
            ],
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testUserRoleCannotBeChanged(): void
    {
        // Arrange
        $user = UserFactory::new(['role' => 'ROLE_USER'])->create();
        $company = CompanyFactory::new()->create();
        $companyAdmin = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN'])->create();


        // Act
        $this->sendPostRequest('/api/users', [
            'name' => 'john Doe',
            'company' => "/api/companies/". $company->getId(),
            'role' => 'ROLE_INVALID', // role field is not a valid choice
        ], $user->object());

        // Assert
        self::assertResponseStatusCodeSame(HttpResponse::HTTP_FORBIDDEN);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testCompanyAdminCanOnlySetUserRole(): void
    {
        // Arrange
        $companyAdmin = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN'])->create();
        $company = CompanyFactory::new()->create();

        // Act
        $this->sendPostRequest('/api/users', [
            'name' => 'john Doe',
            'company' => "/api/companies/". $company->getId(),
            'role' => 'ROLE_SUPER_ADMIN', // role field is not a valid choice
        ], $companyAdmin->object());


        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        self::assertJsonContains([
            'title' => 'An error occurred',
            'violations' => [
                [
                    'propertyPath' => 'role',
                    'message' => 'You are not allowed to set the ROLE_SUPER_ADMIN role',
                ],
            ],
        ]);
    }

//=====================================================================



//=======================Company  field  validation tests=====================

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testUserHasCompanyForUserRole(): void
    {
        // Arrange
        $company = CompanyFactory::new()->create();
        $user = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company])->create();

        // Act
        $response = $this->sendGetRequest('/api/users/' . $user->getId(), $user->object());

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('company', $data);
        $this->assertEquals("/api/companies/".$company->getId(), $data['company']);
    }

    public function testUserHasCompanyForCompanyAdminRole(): void
    {
        // Arrange
        $company = CompanyFactory::new()->create();
        $user = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN', 'company' => $company])->create();

        // Act
        $response = $this->sendGetRequest('/api/users/' . $user->getId(), $user->object());

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('company', $data);
        $this->assertEquals("/api/companies/".$company->getId(), $data['company']);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testUserDoesNotHaveCompanyForSuperAdminRole(): void
    {
        // Arrange
        $user = UserFactory::createOneSuperAdmin();

        // Act
        $response = $this->sendGetRequest('/api/users/' . $user->getId(), $user);

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('company', $data);
    }


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testSuperAdminShouldNotHaveCompany(): void
    {
        // Arrange
        $companyAdmin = UserFactory::new(['role' => 'ROLE_SUPER_ADMIN'])->create();
        $company = CompanyFactory::new()->create();

        // Act
        $response=$this->sendPostRequest('/api/users', [
            'name' => 'john Doe',
            'role' => 'ROLE_SUPER_ADMIN', // role field is not a valid choice
        ], $companyAdmin->object());

        //dd($m->getContent());
        // Assert
        self::assertResponseStatusCodeSame(HttpResponse::HTTP_CREATED);
        $data = json_decode($response->getContent(), true);
        self::assertNotContains('company', $data);
    }

//=====================================================================


//----------------------------------------------------------------Delete operation========================

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RuntimeException
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testDeleteUserAvailableForSuperAdminOnly(): void
    {
        // Arrange
        $company = CompanyFactory::new()->create();
        $user = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company])->create();
        $superAdmin = UserFactory::new(['role' => 'ROLE_SUPER_ADMIN'])->create();

        $id=$user->getId();
        // Act
        $this->sendDeleteRequest('/api/users/' . $user->getId(), $superAdmin->object());
        // Assert
        $userRepository =  self::getContainer()->get(UserRepository::class);
        $deletedUser = $userRepository->find($id);
        $this->assertNull($deletedUser);
    }


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testUserAndCompanyRoleCannotDelete(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $user1 = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        $user2 = UserFactory::new(['role' => 'ROLE_COMPANY_ADMIN', 'company' => $company1])->create();

        // Act
        $this->sendDeleteRequest('/api/users/'.$user1->getId(), $user1->object());
        self::assertResponseStatusCodeSame(HttpResponse::HTTP_FORBIDDEN);

        $this->sendDeleteRequest('/api/users/'.$user2->getId(), $user2->object());
        self::assertResponseStatusCodeSame(HttpResponse::HTTP_FORBIDDEN);
    }


//======================================================================

}
