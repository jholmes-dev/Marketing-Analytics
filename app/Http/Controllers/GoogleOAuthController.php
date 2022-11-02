<?php

namespace App\Http\Controllers;

use App\Services\GoogleOAuthService;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\AnalyticsData;

class GoogleOAuthController extends Controller
{

    /**
     * The instance's Google Client
     */
    public $client;

    /**
     * The the OAuth Service helper class
     */
    public $oauthService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->oauthService = new GoogleOAuthService();
    }

    /**
     * Starts OAuth verification for Google Services
     * 
     */
    public function googleOAuth2Request() 
    {

        // Generate URL
        $auth_url = $this->oauthService->client->createAuthUrl();

        // Redirect user
        return redirect()->away($auth_url);

    }

    /**
     * Starts OAuth verification for Google Services
     * 
     */
    public function googleOAuth2Response(Request $request) 
    {
        
        if (isset($request->code) && !empty($request->code)) 
        {
            $this->oauthService->setOAuthToken($request->code);
            $request->session()->put('access_token', $this->oauthService->generateAccessToken());
            return redirect(route('home'))->with('success', 'Google API authentication has been confirmed!');
        }

        return redirect(route('home'))->with('error', 'Google API authentication has failed!');

    }

}
