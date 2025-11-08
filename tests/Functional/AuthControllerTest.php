<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testLoginRouteExists(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth/login');
        
        // We're just checking if the route exists, not validating the response
        $this->assertResponseStatusCodeSame(401); // Should be 401 for missing credentials
    }
    
    public function testRefreshRouteExists(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth/refresh');
        
        // We're just checking if the route exists, not validating the response
        $this->assertResponseStatusCodeSame(400); // Should be 400 for missing refresh token
    }
    
    public function testLogoutRouteExists(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/auth/logout');
        
        // We're just checking if the route exists, not validating the response
        $this->assertResponseStatusCodeSame(400); // Should be 400 for missing refresh token
    }
}