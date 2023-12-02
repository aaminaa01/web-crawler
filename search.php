<?php 
include 'index.php'; 

// Check if the search query is provided in the URL
if (isset($_GET['query'])) {
    $search_query = $_GET['query'];

    // Perform search in the HTML contents
    // $matching_urls = searchInHtmlContents($html_contents, $search_query);

    // Display search results
    echo '<div class="container mt-5">'; // Bootstrap container class

    echo "<h2 class='mb-4'>Search Results for '$search_query':</h2>"; // Bootstrap margin class

    $matching_urls = searchInDatabase($search_query);

    if (!empty($matching_urls)) {
        // Pass the matching URLs to the display page using URL parameters
        $urlParam = implode(',', $matching_urls);
        header("Location: display.php?urls=$urlParam&query=$search_query");
        exit();

    } else {
        echo "<p class='mt-3'>No results found.</p>"; // Bootstrap margin class
    }

    echo '</div>';

} else {
    echo "<p class='mt-3'>No search query provided.</p>"; // Bootstrap margin class
}

function searchInDatabase($search_query) {
    
    $matching_urls = [];
    include 'db.php';
    // Create a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    $sql = "SELECT url, html_content FROM crawled_urls";

    $result = $conn->query($sql);

    if ($result !== false && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $url = $row['url'];
            $data = $row['html_content'];

            if (!empty($data) && stripos($data, $search_query) !== false) {
                $matching_urls[] = $url;
            }
        }
    }
    return $matching_urls;
}
?>
