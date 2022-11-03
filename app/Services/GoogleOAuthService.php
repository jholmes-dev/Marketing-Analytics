<?php

namespace App\Services;

use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\AnalyticsData;

class GoogleOAuthService 
{
    /**
     * The instance's Google Client
     */
    public $client;

    /**
     * OAuth Token
     */
    public $oauthToken = null;

    /**
     * Constructs a new service
     *
     */
    public function __construct($token = null) 
    {
        $this->client = $this->generateOAuthClient();

        if ($token !== null)
        {
            $this->oauthToken = $token;
        }

    } 
    
    /**
     * Generates a Google API Client for use during OAuth verification
     * 
     * @return Google\Client
     */
    public function generateOAuthClient() 
    {

        // Set up Google API Client
        $client = new Client();
        $client->setAuthConfig(config('app.oauth_secret'));
        $client->addScope(SearchConsole::WEBMASTERS_READONLY);
        $client->addScope(AnalyticsData::ANALYTICS_READONLY);
        $client->setRedirectUri(config('app.oauth_url'));
        $client->setAccessType('offline');

        // Return client
        return $client;

    }

    /**
     * Generates a new access token
     * 
     * @param String $token : The Google OAuth Token to be authenticated with
     */
    public function generateAccessToken()
    {
        $this->client->authenticate($this->oauthToken);
        return $this->client->getAccessToken();
    }

    /**
     * Sets the object's oauth token
     * 
     */
    public function setOAuthToken($token)
    {
        $this->oauthToken = $token;
    }

}