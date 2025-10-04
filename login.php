<?php
session_start();
$error = '';

// Handle login

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_form'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = new mysqli("localhost", "root", "", "tcps");
    if($conn->connect_error){ die("Connection failed: " . $conn->connect_error); }

    $stmt = $conn->prepare("SELECT password FROM admins WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $stmt->bind_result($hash);
        $stmt->fetch();

        if(password_verify($password, $hash)){
            $_SESSION['is_admin'] = true;
            $_SESSION['username'] = $username;
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Invalid username";
    }

    $stmt->close();
    $conn->close();
}



if(isset($_GET['logout'])){
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login / Dashboard</title>
  <style>
   
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{background:#f0f7ff;color:#333;line-height:1.6;}
    #header{background:#fff;box-shadow:0 2px 6px rgba(0,0,0,0.08);position:sticky;top:0;z-index:1000;}
    #head_container{display:flex;justify-content:space-between;align-items:center;padding:1rem 3rem;max-width:1200px;margin:auto;}
    .logo{width:150px;}
    .nav_links{list-style:none;display:flex;gap:2rem;}
    .nav_links li a{text-decoration:none;color:#333;font-weight:500;transition:color 0.3s ease;}
    .nav_links li a:hover{color:#007bff;}
    #login_section{padding:4rem 2rem;display:flex;justify-content:center;align-items:center;min-height:70vh;}
    .login_box{background:#fff;padding:2.5rem 2rem;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.08);width:100%;max-width:400px;text-align:center;}
    .login_box h2{color:#007bff;margin-bottom:1.5rem;}
    .login_form{display:flex;flex-direction:column;gap:1rem;}
    .login_form input{padding:0.8rem 1rem;border-radius:6px;border:1px solid #ccc;font-size:1rem;transition:border 0.3s ease,box-shadow 0.3s ease;}
    .login_form input:focus{outline:none;border-color:#007bff;box-shadow:0 0 5px rgba(0,123,255,0.3);}
    #login{padding:0.8rem 1.6rem;border:none;border-radius:6px;background-color:#28a745;color:#fff;font-weight:500;cursor:pointer;transition:background 0.3s ease,transform 0.2s ease;}
    #login:hover{background-color:#1e7e34;transform:translateY(-2px);}
    .login_box p{margin-top:1rem;font-size:0.9rem;color:#555;}
    .login_box p a{color:#007bff;text-decoration:none;}
    .login_box p a:hover{text-decoration:underline;}
    table{border-collapse:collapse;width:100%;max-width:900px;margin:2rem auto;}
    th, td{border:1px solid #ccc;padding:0.8rem;text-align:left;}
    th{background:#007bff;color:#fff;}
    tr:nth-child(even){background:#e6f0ff;}
    .logout{margin:2rem auto;text-align:center;}
    .logout a{padding:0.6rem 1.2rem;background:#ff4d4d;color:#fff;border-radius:6px;text-decoration:none;}
    .logout a:hover{background:#cc0000;}
    @media(max-width:768px){
  #head_container {
    flex-direction: column;
    gap: 1rem;
  }
  .nav_links {
    justify-content: center;
  }
  table {
    display: block;
    overflow-x: auto;
  }
}
  </style>
</head>
<body>

<header id="header">
  <div id="head_container">
    <img src="imgs/tcps_h_logo.png" alt="Logo" class="logo">
    <ul class="nav_links">
      <li><a href="index.html">Home</a></li>
      <li><a href="services.html">Services</a></li>
      <li><a href="about.html">About Us</a></li>
    </ul>
  </div>
</header>

<?php if(!isset($_SESSION['is_admin'])): ?>
<!-- Login Section -->
<section id="login_section">
  <div class="login_box">
    <h2>Admin Login</h2>
    <?php if(!empty($error)) echo '<p style="color:red;">'.$error.'</p>'; ?>
  <form class="login_form" method="post" action="">
  <input type="hidden" name="login_form" value="1">
  <input type="text" name="username" placeholder="Username" required>
  <input type="password" name="password" placeholder="Password" required>
  <button id="login" type="submit">Login</button>
</form>

  </div>
</section>

<?php else: ?>
<!-- Dashboard Section -->
<h2 style="text-align:center; color:#007bff; margin-top:2rem;">Requests Dashboard</h2>

<?php
$conn = new mysqli("localhost", "root", "", "tcps");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Handle deletion
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])){
    $delete_id = intval($_POST['delete_id']);
    $del_stmt = $conn->prepare("DELETE FROM requests WHERE id=?");
    $del_stmt->bind_param("i", $delete_id);
    $del_stmt->execute();
    $del_stmt->close();
}

// Fetch requests
$result = $conn->query("SELECT id, name, email, message, reg_date FROM requests ORDER BY reg_date DESC");

if($result->num_rows > 0):
?>
<table>
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Message</th>
    <th>Submitted At</th>
    <th>Action</th>
  </tr>
  <?php while($row = $result->fetch_assoc()): ?>
  <tr>
    <td><?php echo htmlspecialchars($row['id']); ?></td>
    <td><?php echo htmlspecialchars($row['name']); ?></td>
    <td><?php echo htmlspecialchars($row['email']); ?></td>
    <td><?php echo htmlspecialchars($row['message']); ?></td>
    <td><?php echo htmlspecialchars($row['reg_date']); ?></td>
    <td>
      <form method="post" style="margin:0;">
        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
        <button type="submit" style="padding:0.3rem 0.6rem; background:#ff4d4d; color:#fff; border:none; border-radius:4px; cursor:pointer;">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php else: ?>
<p style="text-align:center;">No requests found.</p>
<?php endif; $conn->close(); ?>


<div class="logout">
  <a href="?logout=1">Logout</a>
</div>

<?php endif; ?>

</body>
</html>
