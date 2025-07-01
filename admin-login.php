<?php
session_start();
if(isset($_POST['username']) && isset($_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if($user == 'Admin' && $pass == '123') {
        $_SESSION['admin'] = true;
        header("Location: admin-dashboard.php");
        exit();
    } else {
        echo "Invalid admin credentials.";
    }
}
?>
<form method="POST">
  Username: <input name="username" /><br />
  Password: <input type="password" name="password" /><br />
  <button type="submit">Login</button>
</form>