<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\AnalyticsData;

class GoogleOAuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Starts OAuth verification for Google Services
     * 
     */
    public function googleOAuth2Request() 
    {

        // Set up Google API Client
        $client = $this->generateOAuthClient();

        // Generate URL
        $auth_url = $client->createAuthUrl();

        // Redirect user
        return redirect()->away($auth_url);

    }

    /**
     * Starts OAuth verification for Google Services
     * 
     */
    public function googleOAuth2Response(Request $request) 
    {

        // Set up Google API Client
        $client = $this->generateOAuthClient();
        
        if (isset($request->code) && !empty($request->code)) {

            $client->authenticate($request->code);
            $request->session()->put('access_token', $client->getAccessToken());
            
            return redirect(route('home'))->with('success', 'Google API authentication has been confirmed!');

        }

        return redirect(route('home'))->with('error', 'Google API authentication has failed!');

    }

    /**
     * Generates a Google API Client for use during OAuth verification
     * 
     * @return Google\Client
     */
    private function generateOAuthClient() 
    {

        // Set up Google API Client
        $client = new Client();
        $client->setAuthConfig(env('GOOGLE_CLIENT_SECRET', false));
        $client->addScope(SearchConsole::WEBMASTERS_READONLY);
        $client->addScope(AnalyticsData::ANALYTICS_READONLY);
        $client->setRedirectUri(env('GOOGLE_OAUTH_URL', 'http://localhost:8000/oauthresponse'));

        // Return client
        return $client;

    }

}
