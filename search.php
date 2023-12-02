<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Results</title>
    <!-- Add your own styles or use Bootstrap for styling -->
    <!-- Example Bootstrap CDN link -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <?php
        // Check if the search query is provided in the URL
            if (isset($_GET['query'])) {
                $search_query = $_GET['query'];


                echo "<h2 class='mb-4'>Search Results for '$search_query':</h2>"; // Bootstrap margin class

                $matching_urls = searchInDatabase($search_query);

                if (!empty($matching_urls)) {
                    echo "<ul class='list-group'>"; // Bootstrap list group class
                    foreach ($matching_urls as $url) {
                        echo "<li class='list-group-item'><a href='$url'>$url</a></li>"; // Bootstrap list group item class
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='mt-3'>No results found.</p>"; // Bootstrap margin class
                }

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
    </div>
</body>
</html>


