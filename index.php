<?php
    $seed_url = "https://en.wikipedia.org/wiki/Pain_au_chocolat";
    $url_queue = array($seed_url);
    $crawled = array();
    $depth_limit = 2;
    $html_contents = array();
    echo "<h1>Web Crawler</h1>";

    function crawl($url, $current_depth = 1) {
        global $ch, $crawled, $url_queue, $html_contents, $depth_limit;
        $urls_in_current_page = array();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Execute cURL session and fetch HTML content
        $html = curl_exec($ch);

        // Check if HTML content is empty or cURL has an error
        if (empty($html) || curl_errno($ch)) {
            echo "Skipping URL: $url due to empty or inaccessible HTML content.";
            curl_close($ch);
            return;
        }

        // put this url into crawled array
        $crawled[] = $url;

        // remove this url from url_queue
        $url_queue = array_diff($url_queue, array($url));

        // Extract the URLs from the HTML content and add them to the url_queue
        $extracted_urls = extractAnchorTags($html);

        // Save the HTML content in the array
        $html_contents[$url] = $html;

        foreach ($extracted_urls as $url) {
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

        // Load HTML content into the DOMDocument 
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
        // foreach ($result as $url) {
        //    echo $url . "<br>";
        // }
        return $result;
    }

    
    crawl($seed_url);

    saveHtmlToDatabase($html_contents);
?>
