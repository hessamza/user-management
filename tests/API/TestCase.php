<?php

namespace App\Tests\API;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Response;
use App\Entity\User;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Base test case class for API tests.
 */
abstract class TestCase extends ApiTestCase
{
    /**
     * Sends a GET request to the specified URL with the given user object as the authenticated user.
     *
     * @param string $url  The URL to send the GET request to.
     * @param User   $user The authenticated user object.
     *
     * @return Response The response object.
     *
     * @throws TransportExceptionInterface If a transport error occurs.
     */
    protected function sendGetRequest(string $url, User $user): Response
    {
        $token = $this->getContainer()->get('lexik_jwt_authentication.jwt_manager')->create($user);

        $client = static::createClient();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $client->request(
            'GET',
            $url,
            ['headers' => $headers]
        );

        return $client->getResponse();
    }

    /**
     * Sends a POST request to the specified URL with the given data and user object as the authenticated user.
     *
     * @param string $url  The URL to send the POST request to.
     * @param array  $data The data to send in the request body.
     * @param User   $user The authenticated user object.
     *
     * @return Response|null The response object, or null if an error occurs.
     *
     * @throws TransportExceptionInterface If a transport error occurs.
     */
    protected function sendPostRequest(string $url, array $data, User $user): ?Response
    {
        $client = self::createClient();
        $token = $this->getContainer()->get('lexik_jwt_authentication.jwt_manager')->create($user);
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        $client->request('POST', $url, [
            'headers' => $headers,
            'json' => $data,
        ]);

        return $client->getResponse();
    }

    /**
     * Sends a DELETE request to the specified URL with the given user object as the authenticated user.
     *
     * @param string $url  The URL to send the DELETE request to.
     * @param User   $user The authenticated user object.
     *
     * @throws TransportExceptionInterface If a transport error occurs.
     * @throws ServerExceptionInterface    If a server error occurs.
     * @throws RedirectionExceptionInterface If a redirection error occurs.
     * @throws ClientExceptionInterface    If a client error occurs.
     */
    protected function sendDeleteRequest(string $url, User $user): void
    {
        $token = $this->getContainer()->get('lexik_jwt_authentication.jwt_manager')->create($user);
        $client = static::createClient();
        $client->request('DELETE', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}