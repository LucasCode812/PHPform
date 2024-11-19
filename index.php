<?php
// Load Composer's autoloader if you haven't already
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Access environment variables
$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];
$db = $_ENV['DB_NAME'];
$ssl_key = $_ENV['SSL_KEY_PATH'];
$ssl_cert = $_ENV['SSL_CERT_PATH'];
$ca_cert = $_ENV['CA_CERT_PATH'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitize and validate the input from the form
  $entry = filter_input(INPUT_POST, 'entry', FILTER_SANITIZE_STRING);

  // Connect to the MySQL database (on the external server)
  $mysqli = new mysqli();

  // Set SSL parameters
  $mysqli->ssl_set($ssl_key, $ssl_cert, $ca_cert, null, null);

  // Connect to the MySQL database (on the external server)
  if (!$mysqli->real_connect($host, $user, $password, $db, 3306, null, MYSQLI_CLIENT_SSL)) {
    error_log('Connection failed: ' . $mysqli->connect_error); // Log error
    die('An error occurred. Please try again later.');
  }

  // Prepare and bind
  $stmt = $mysqli->prepare("INSERT INTO form_entries (entry_text) VALUES (?)");
  if ($stmt === false) {
    error_log('Prepare failed: ' . $mysqli->error); // Log error
    die('An error occurred. Please try again later.');
  }
  $stmt->bind_param('s', $entry);

  // Execute the query
  if ($stmt->execute()) {
    echo 'Entry added successfully!';
  } else {
    error_log('Execution error: ' . $stmt->error); // Log error
    echo 'An error occurred. Please try again later.';
  }

  // Close the statement and connection
  $stmt->close();
  $mysqli->close();
}
?>

<!-- Simple HTML Form -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" />
<style>
  html,
  body {
    height: 100%;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
  }

  form {
    width: 30%;
    text-align: center;
  }
</style>
<form style="width: 30%;" method="POST">
  <label for="entry">Enter Text:</label>
  <input type="text" id="entry" name="entry" required><br><br>
  <button type="submit">Submit</button>
</form>