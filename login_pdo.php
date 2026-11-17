<?php
// connect to MySQL database using PDO
$dsn = "mysql:host=localhost;dbname=pib_portal_db;charset=utf8mb4";
$pdo_conn = new PDO($dsn, "root", "", [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_EMULATE_PREPARES => false
]);

$status_text = "";
$found_accounts = [];

// [PART 1] - prevent sql injection by using prepared statements and parameter binding
if (isset($_POST['login_input'])) {
    // [STEP 1] - send the query skeleton with placeholders to mysql
    $stmt = $pdo_conn->prepare("SELECT * FROM accounts WHERE login_name = :login_param AND pass_code = :pass_param");

    // [STEP 2] - bind the user input values to the placeholders in the prepared statement
    $stmt->execute([
      'login_param' => $_POST['login_input'],
      'pass_param'  => $_POST['pass_input'] ?? ''
    ]);

    // fetch the first row of the result and display the login status
    $account_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account_row) {
      $status_text = "Login successful! Welcome, " . htmlspecialchars($account_row['login_name']) . " (Role: " . $account_row['access_level'] . ")";
    } else {
      $status_text = "Wrong username or password.";
    }
}

// [PART 2] - secure numeric lookup using strict integer parameter binding
if (isset($_GET['acc_id'])) {
  // prepare the statement with a placeholder for the account ID
  $stmt = $pdo_conn->prepare("SELECT acc_id, login_name, contact_mail, access_level FROM accounts WHERE acc_id = :id_param");
  $stmt->bindValue(':id_param', (int)$_GET['acc_id'], PDO::PARAM_INT);
  $stmt->execute();

  // fetch all matching accounts 
  $found_accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
  <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Portal Login - Secure PDO</title>
      <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 40px; }
        .box { background: #fff; padding: 25px; max-width: 520px; margin: auto; border: 1px solid #ccc; }
        input { width: 95%; padding: 8px; margin: 6px 0 14px 0; }
        button { padding: 8px 16px; cursor: pointer; }
        .msg { background: #eee; padding: 10px; margin-top: 15px; }
      </style>
    </head>

    <body>
      <div class="box">
        <h2>Test Portal (Secure PDO)</h2>
        <form method="POST" action="login_pdo.php">
          <label>Username:</label><br>
          <input type="text" name="login_input" placeholder="Enter username"><br>

          <label>Password:</label><br>
          <input type="password" name="pass_input" placeholder="Enter password"><br>
          
          <button type="submit">Sign In</button>
        </form>

        <?php if ($status_text !== ""): ?>
          <div class="msg"><strong>Result:</strong> <?= $status_text ?></div>
        <?php endif; ?>

        <hr style="margin: 25px 0;">
        <h3>Account Info Lookup</h3>
        <p>Test link: <a href="login_pdo.php?acc_id=1">Load Account #1</a></p>

        <?php if (!empty($found_accounts)): ?>
          <pre><?php print_r($found_accounts); ?></pre>
        <?php endif; ?>
      </div>
    </body>
  </html>