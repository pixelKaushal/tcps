<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tcps";

// Connect
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "CREATE TABLE IF NOT EXISTS admins(
    username VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL
)";
$sql2 = "
CREATE TABLE IF NOT EXISTS requests(
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$conn->query($sql);
$conn->query($sql2);
if ($conn->query($sql2) === TRUE) {
    echo "Table requests created successfully!";
} else {
    echo "Error creating requests table: " . $conn->error;
}
//logic insert to requewsts
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO requests (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        header("Location: index.html?success=1");   
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
// Insert default admin safely
$adminUser = "bamdev2025";
$adminPass = password_hash("topmanufan", PASSWORD_DEFAULT);

$conn->query("INSERT INTO admins (username, password) 
              VALUES ('$adminUser', '$adminPass') 
              ON DUPLICATE KEY UPDATE username=username");

echo "Admin bamdev2025 has been added successfully!";
$conn->close();
?>
