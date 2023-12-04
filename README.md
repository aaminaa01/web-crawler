# The Itsy Bitsy Spider

This web crawler allows you to search for a string within websites crawled from all over the web. The crawling modules gathers information about websites, including their titles, meta descriptions and content, and persistently stores them in a database. Users may then search for a certain string using the intuitive search interface.

## Technologies Used

### Languages:
- **PHP:** Used for server-side scripting to handle the backend logic, interact with the database, and generate dynamic content.
- **HTML:** Utilized for creating the structure and content of the web pages, defining the user interface elements.
- **SQL:** Employed for managing and querying the MySQL database, handling data storage and retrieval.

### Libraries:
- **cURL (Client URL):** Used for making HTTP requests to fetch the HTML content of web pages. It facilitates the web crawling process by retrieving information from external websites.
- **DOMDocument:** Utilized for HTML parsing, allowing the extraction of specific elements and content from the fetched web pages. This is essential for analyzing and storing relevant data.

### Database:
- **MySQL:** Chosen as the relational database management system to store crawled data. It provides a structured and efficient way to organize information, making it easily accessible for retrieval and analysis.

### Styling:
- **Bootstrap:** Implemented for styling and layout purposes, ensuring a consistent and visually appealing user interface. Bootstrap's responsive design elements enhance the application's accessibility across various devices and screen sizes.

## Setting Up and Running the Spider

### Prerequisites

- PHP (v.8.3.0 or above)
- XAMPP Server

1. Download the zipped code folder or clone this repository:

   ```sh
   git clone [https://github.com/aaminaa01/web-crawler.git]
   ```

3. Put the downloaded or cloned (unzipped) folder in the htdocs folder (inside the xampp folder) e.g. on my machine the file path after putting this code folder in htdocs (present in D://) will be: D:\xampp\htdocs\web-crawler-main.

4. Run the XAMPP server.

5. Open any browser, and to setup the spider and crawl content from the seed URL, type the following into the search bar:

   ```sh
   http://localhost/web-crawler-main/index.php
   ```
6. Now to search for any string within the crawled content, type the following into the search bar:

   ```sh
   http://localhost/web-crawler-main/home.html
   ```

7. You can now search for strings in the crawled content.

## User Interface

### 1. Landing Page

<img width="960" alt="homepg_starting" src="https://github.com/aaminaa01/web-crawler/assets/114048193/ff2c837f-0859-4483-8bcd-c886ff261e52">


### 2. Entering a Query in the Search Bar

<img width="956" alt="query2" src="https://github.com/aaminaa01/web-crawler/assets/114048193/0e456c27-fe60-4dd9-b66a-c47065ca7ce7">


### 3. Search Results
<img width="946" alt="result2" src="https://github.com/aaminaa01/web-crawler/assets/114048193/79026e72-ab7f-4ac4-80e0-907e8f430305">

### 4. Database Schema

<img width="276" alt="schema" src="https://github.com/aaminaa01/web-crawler/assets/114048193/9fac2dc0-df29-4eed-992f-f90957dbe492">

