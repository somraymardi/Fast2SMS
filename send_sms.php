<?php
session_start();
include 'db.php';

if(!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$number = $_POST['number'];
$message = $_POST['message'];
$user = $_SESSION['username'];

// Check wallet balance
$sql = "SELECT balance FROM users WHERE username='$user'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$balance = $row['balance'];

if($balance < 1) {
    echo "Insufficient wallet balance. Please recharge your wallet.";
    echo '<br><a href="wallet.php">Go to Wallet</a>';
    exit();
}

// Deduct 1 credit per SMS (for simplicity)
$new_balance = $balance - 1;
$conn->query("UPDATE users SET balance=$new_balance WHERE username='$user'");

// Prepare data for Fast2SMS API
$data = array(
    "sender_id" => "FSTSMS",
    "message" => $message,
    "language" => "english",
    "route" => "p",
    "numbers" => $number,
);

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode($data),
  CURLOPT_HTTPHEADER => array(
    "authorization: 47WcGQMuHXNp3KORjsd8rDFtz6vxm51EyZgJ9CekUblnVfi0ao9pG5CSLXr0NvJ3aZkPdQoylfwDshA1",
    "accept: */*",
    "cache-control: no-cache",
    "content-type: application/json"
  ),
));

$response = curl_exec($curl);
curl_close($curl);

// Save SMS log
$stmt = $conn->prepare("INSERT INTO sms_logs (username, mobile, message, status) VALUES (?, ?, ?, ?)");
$status = $response ? "Sent" : "Failed";
$stmt->bind_param("ssss", $user, $number, $message, $status);
$stmt->execute();

echo "<h2>SMS Status: $status</h2>";
echo "<p>Number: $number</p>";
echo "<p>Message: $message</p>";
echo "<p>Remaining Balance: $new_balance</p>";
echo '<a href="dashboard.php">Back to Dashboard</a>';
?>