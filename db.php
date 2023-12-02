<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "web_crawler";

    $conn = new mysqli($servername, $username, $password);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE DATABASE IF NOT EXISTS $dbname;
            USE $dbname;
            CREATE TABLE IF NOT EXISTS crawled_urls (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                url VARCHAR(255) NOT NULL,
                title VARCHAR(255),
                meta_description VARCHAR(255),
                html_content TEXT NOT NULL
            )";
    $conn->multi_query($sql);
    $conn->close();
?>