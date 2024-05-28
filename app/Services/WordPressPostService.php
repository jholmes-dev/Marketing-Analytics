<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Handles retrieval of data from recent posts for a given website
 * Ties into WordPress' REST API
 */
class WordPressPostService {
    
    /**
     * The blog URL we'll be sending requests to
     * 
     * @var String eg: https://example.com
     */
    public $blogUrl;

    /**
     * The WordPress REST API path to retrieve Posts
     * 
     * @var String
     */
    public $restPostPath = "/wp-json/wp/v2/posts/";

    /**
     * Holds the service instance's posts
     * See https://developer.wordpress.org/rest-api/reference/posts/
     * 
     * @var Array<PostData>
     */
    public $posts = [];

    /**
     * Constructs a new service
     *
     */
    public function __construct() {
        //
    } 

    /**
     * Sets the blogUrl Property
     */
    public function setBlogUrl($url)
    {
        $this->blogUrl = $url;
    }

    /**
     * Sends a GET request to load the posts from the URL
     * Accepts date range for posts
     * 
     * @param Date $startDate YYYY-mm-dd
     * @param Date $endDate YYYY-mm-dd
     */
    public function loadPosts($startDate = null, $endDate = null)
    {
        if (!$this->blogUrl) return;

        $urlParams = "?per_page=50";
        
        if ($startDate != null)
        {
            $startDate = new \DateTime($startDate);
            $urlParams .= "&after=" . $startDate->format('Y-m-d') . "T00:00:00";
        }
        if ($endDate != null)
        {
            $endDate = new \DateTime($endDate);
            $urlParams .= "&before=" . $endDate->format('Y-m-d') . "T00:00:00";
        }

        $response = Http::get($this->blogUrl . $this->restPostPath . $urlParams);

        if (!$response->successful()) return;

        $this->posts = $response->json();
    }

    /**
     * Returns the post data array
     * 
     * @return Array<PostData> See: https://developer.wordpress.org/rest-api/reference/posts/
     */
    public function getPostData()
    {
        return $this->posts;
    }
    
}