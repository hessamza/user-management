<?php

namespace API;

use App\DataFixtures\Factory\CompanyFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Tests\API\TestCase;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CompanyTest extends TestCase
{
    use Factories;
    use ResetDatabase;

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetCompaniesShowsAllCompanies(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();
        $user = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();
        // Act
        $response = $this->sendGetRequest('/api/companies', $user->object());

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data['hydra:member']);
    }

    public function testGetCompanyShowsCompanyById(): void
    {
        // Arrange
        $company = CompanyFactory::new()->create();
        $user = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company])->create();
        // Act
        $response = $this->sendGetRequest('/api/companies/' . $company->getId(), $user->object());

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK);
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($company->getId(), $data['id']);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetCompaniesShowsAllCompaniesForCompanyAndSuperAdmin(): void
    {
        // Arrange
        $company1 = CompanyFactory::new()->create();
        $company2 = CompanyFactory::new()->create();

        // Company User
        $companyUser = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company1])->create();

        // Super Admin User
        $superAdminUser = UserFactory::createOneSuperAdmin();

        // Act
        $responseCompanyUser = $this->sendGetRequest('/api/companies', $companyUser->object());
        $responseSuperAdminUser = $this->sendGetRequest('/api/companies', $superAdminUser);

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK, $responseCompanyUser->getStatusCode());
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK, $responseSuperAdminUser->getStatusCode());

        $this->assertJson($responseCompanyUser->getContent());
        $this->assertJson($responseSuperAdminUser->getContent());

        $dataCompanyUser = json_decode($responseCompanyUser->getContent(), true);
        $dataSuperAdminUser = json_decode($responseSuperAdminUser->getContent(), true);

        $this->assertCount(2, $dataCompanyUser['hydra:member']);
        $this->assertCount(2, $dataSuperAdminUser['hydra:member']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCompanyShowsCompanyByIdForCompanyAndSuperAdmin(): void
    {
        // Arrange
        $company = CompanyFactory::new()->create();

        // Company User
        $companyUser = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company])->create();

        // Super Admin User
        $superAdminUser = UserFactory::createOneSuperAdmin();

        // Act
        $responseCompanyUser = $this->sendGetRequest('/api/companies/' . $company->getId(), $companyUser->object());
        $responseSuperAdminUser = $this->sendGetRequest('/api/companies/' . $company->getId(), $superAdminUser);

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK, $responseCompanyUser->getStatusCode());
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_OK, $responseSuperAdminUser->getStatusCode());

        $this->assertJson($responseCompanyUser->getContent());
        $this->assertJson($responseSuperAdminUser->getContent());

        $dataCompanyUser = json_decode($responseCompanyUser->getContent(), true);
        $dataSuperAdminUser = json_decode($responseSuperAdminUser->getContent(), true);

        $this->assertEquals($company->getId(), $dataCompanyUser['id']);
        $this->assertEquals($company->getId(), $dataSuperAdminUser['id']);
    }


    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateCompanyNotAllowedForUserAndCompany(): void
    {
        // Arrange
        $company = CompanyFactory::new()->create();
        $user = UserFactory::new(['role' => 'ROLE_USER', 'company' => $company])->create();
        $companyData = [
            'name' => 'Test Company',
        ];

        // Act
        $response = $this->sendPostRequest('/api/companies', $companyData, $user->object());

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function testCreateCompanyAvailableForSuperAdminOnly(): void
    {
        // Arrange
        $superAdminUser = UserFactory::createOneSuperAdmin();
        $companyData = [
            'name' => 'Test Company',
        ];

        // Act
        $response = $this->sendPostRequest('/api/companies', $companyData, $superAdminUser);

        // Assert
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_CREATED, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Test Company', $data['name']);


        // Validate name field requirements
        $this->assertArrayHasKey('name', $data); // Check if the response contains the 'name' field
        $this->assertIsString($data['name']); // Check if the 'name' field is a string
        $this->assertLessThanOrEqual(
            100,
            strlen($data['name'])
        ); // Check if the 'name' field has a maximum of 100 characters
        $this->assertGreaterThanOrEqual(
            5,
            strlen($data['name'])
        ); // Check if the 'name' field has a minimum of 5 characters

        // Check uniqueness of the name field in the database
        $duplicateCompanyData = [
            'name' => 'Test Company', // Use the same name as before
        ];
        $this->sendPostRequest('/api/companies', $duplicateCompanyData, $superAdminUser);
        $this->assertResponseStatusCodeSame(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        self::assertJsonContains([
            'title' => 'An error occurred',
            'violations' => [
                [
                    'propertyPath' => 'name',
                    'message' => 'This value is already used.',
                ],
            ],
        ]);
    }
}
