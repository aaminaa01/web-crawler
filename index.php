<?php
    $time_limit = 1000;
    set_time_limit($time_limit);
    $seed_url = "https://en.wikipedia.org/wiki/Pain_au_chocolat";
    $url_queue = array($seed_url);
    $crawled = array();
    $urls_disallowed = array();
    $depth_limit = 1;
    $html_contents = array();

    echo "<h1>Crawling the web...</h1>";

    function crawl($url, $current_depth = 0) {
        global $ch, $crawled, $url_queue, $html_contents, $depth_limit, $user_agent;
        $urls_in_current_page = array();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        // Execute cURL session and fetch HTML content
        $html = curl_exec($ch);

        // Check if HTML content is empty or cURL has an error
        if (empty($html) || curl_errno($ch)) {
            // echo "Skipping URL: $url due to empty or inaccessible HTML content.";
            curl_close($ch);
            return;
        }

        // put this url into crawled array
        $crawled[] = $url;

        // remove this url from url_queue
        $url_queue = array_diff($url_queue, array($url));

        // Extract the URLs from the HTML content and add them to the url_queue
        $extracted_urls = extractAnchorTags($html);

        // Parse the robots.txt file of this url to determine which of its links are disallowed
        parseRobotsTxt($url);

        // Filter the URLs and keep the ones which are allowed to be crawled
        $allowed_urls = array_filter($extracted_urls, 'isCrawlingAllowed');

        // Save the HTML content in the array
        $html_contents[$url] = $html;

        foreach ($allowed_urls as $url) {
            if (!in_array($url, $crawled) && !in_array($url, $url_queue)) {
                $url_queue[] = $url;
                $urls_in_current_page[] = $url;
            }
        }

        if ($current_depth >= $depth_limit) {
            return;
        }
        else{
            foreach ($urls_in_current_page as $url) {
                crawl($url, $current_depth + 1);
            }
        }

        // Close cURL session
        curl_close($ch);
    }

    function saveHtmlToDatabase($html_contents) {
        include 'db.php';
        // Create a connection to the database
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Delete fromthe crawled_urls table
        $conn->query("DELETE FROM crawled_urls");

        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO crawled_urls (url, title, meta_description, html_content) VALUES (?, ?, ?, ?)");
        foreach ($html_contents as $url => $data) {
            $dom = new DOMDocument;
            libxml_use_internal_errors(true); // Disable libxml errors
            // Load HTML content into the DOMDocument
            $dom->loadHTML($data);
            libxml_clear_errors(); // Clear any libxml errors  
            // Get the title tag from the DOMDocument
            $titleElement = $dom->getElementsByTagName('title')->item(0);
            $title = ($titleElement !== null) ? $titleElement->textContent : null;

            // Get the meta description tag from the DOMDocument
            $metaElement = $dom->getElementsByTagName('meta')->item(0);
            $meta_description = ($metaElement !== null && $metaElement->hasAttribute('content')) ? $metaElement->getAttribute('content') : null;
            $stmt->bind_param("ssss", $url, $title, $meta_content, $data);
            $stmt->execute();
        }
        $stmt->close();
        $conn->close();
    }
    
    function extractAnchorTags($html) {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true); // Disable libxml errors

        // Load HTML content into the DOMDocument without adding <html>, <body>, etc.
        $dom->loadHTML($html);

        libxml_clear_errors(); // Clear any libxml errors  

        // Find all anchor (a) tags in the HTML
        $anchorTags = $dom->getElementsByTagName('a');

        $result = [];
        foreach ($anchorTags as $anchor) {
            // Get the href attribute of each anchor tag
            $href = $anchor->getAttribute('href');

            // Check if href starts with http or https
            if (stripos($href, 'http') === 0) {
                // Add the href value to the result array
                $result[] = $href;
            }
        }
        foreach ($result as $url) {
            // echo $url . "<br>";
        }
        return $result;
    }

    function isCrawlingAllowed($url) {
        global $urls_disallowed;

        foreach ($urls_disallowed as $disallowed) {
            // Check if the URL starts with or is equal to the disallowed part
            if (strpos($url, $disallowed) === 0 || $url === $disallowed) {
                return false;  // Crawling is not allowed
            }
        }
        return true; // Crawling is allowed
    }

    // Returns robots.txt, if it exists, for the given URL
    function getRobotsTxt($url){
        if (substr($url, -1) === '/') {
            // Remove the trailing slash
            $url = rtrim($url, '/');
        }

        $robotsTxt = $url . '/robots.txt';

        // Try to get robots.txt
        $robotsContent = @file_get_contents($robotsTxt);

        // Check if fetching the robots.txt file was successful
        if ($robotsContent === false) {
            return '';
        }
        else{
            return $robotsContent;
        }

    }

    // Parses the robots.txt file of the URL to determine which of its links are disallowed, and puts them in the $urls_disallowed array
    function parseRobotsTxt($url) {
        global $urls_disallowed;

        // Get the robots.txt file for this url
        $robotsContent = getRobotsTxt($url);

        // if robots.txt didn't exist, then all links are allowed
        if (empty($robotsContent)) {
            return;
        }
        
        try {
            // Initialize variables
            $currentUserAgent = null;
            $disallowedPaths = [];

            // first line of robots.txt file
            $line = strtok($robotsContent, "\r\n");

            while (false !== $line) {
                // skip blank lines
                if (!$line = trim($line)) {
                    // Get the next line
                    $line = strtok("\r\n");
                    continue;
                }

                // Check if start of line matches 'User-agent:'
                if (strpos($line, 'User-agent: *') === 0) {
                    // Get the user agent
                    $currentUserAgent = '*';
                    $disallowedPaths = [];  // Reset disallowed paths for the new user agent
                } elseif ($currentUserAgent !== null && strpos($line, 'Disallow:') === 0) {
                    // Get the disallowed path
                    $disallowedPath = trim(substr($line, strlen('Disallow:')));
                    if ($disallowedPath === '/') {
                        // If the disallowed path is '/', then all links are disallowed
                        $urls_disallowed[] = $url;
                    } else {
                        // Add the disallowed path to the $urls_disallowed array
                        $urls_disallowed[] = $url . $disallowedPath;
                    }
                    // Collect disallowed paths for the current user agent
                    $disallowedPaths[] = $disallowedPath;
                } elseif ($currentUserAgent !== null && strpos($line, 'User-agent:') === 0) {
                    // If a new user agent is encountered, add the collected disallowed paths to the global array
                    foreach ($disallowedPaths as $path) {
                        $urls_disallowed[] = $url . $path;
                    }
                    // Get the new user agent
                    $currentUserAgent = trim(substr($line, strlen('User-agent:')));
                    $disallowedPaths = [];  // Reset disallowed paths for the new user agent
                }

                // Get the next line
                $line = strtok("\r\n");
            }

            // Add the remaining disallowed paths for the last user agent
            foreach ($disallowedPaths as $path) {
                $urls_disallowed[] = $url . $path;
            }

    } catch (Exception $e) {
        return;
        }
    }
    
    crawl($seed_url);

    saveHtmlToDatabase($html_contents);

    echo "<h1>Ready to search in crawled content.</h1>";
?>
