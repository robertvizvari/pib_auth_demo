<?php
// disable strict exceptions in PHP, so mysql errors who up on screen
mysqli_report(MYSQLI_REPORT_OFF);

// connect to local xampp mysql database
$db_link = mysqli_connect("localhost", "root", "", "pib_portal_db");
if (!$db_link) {
  die("Database connection error: " . mysqli_connect_error());
}

$status_text = "";
$found_accounts = [];

// [PART 1] - vulnerable login form (string concatenation)
if (isset($_POST['login_input'])) {
  $entered_login = $_POST['login_input'];
  $entered_pass = $_POST['pass_input'] ?? '';

  // [UNSAFE] - user input is glued directly into the sql command without any checks
  $query_str = "SELECT * FROM accounts WHERE login_name = '" . $entered_login . "' AND pass_code = '" . $entered_pass . "'";
  $query_res = mysqli_query($db_link, $query_str);

  if (!$query_res) {
    // showing exact db error helps attackers map the query structure
    $status_text = "Database error: " . mysqli_error($db_link);
  } elseif ($account_row = mysqli_fetch_assoc($query_res)) {
    // logs in as the first returned row (which is admin_root if WHERE evaluates to true for all rows)
    $status_text = "Login successful! Welcome, " . htmlspecialchars($account_row['login_name']) . " (Role: " . $account_row['access_level'] . ")";
  } else {
    $status_text = "Wrong username or password.";
  }
}

// [PART 2] - vulnerable account lookup via URL parameter (numeric concatenation)
if (isset($_GET['acc_id'])) {
  $lookup_id = $_GET['acc_id'];

  // [UNSAFE] - numeric id is not wrapped in quotes, making UNION and boolean injections trivial
  $id_query = "SELECT acc_id, login_name, contact_mail, access_level FROM accounts WHERE acc_id = " . $lookup_id;
  $id_res = mysqli_query($db_link, $id_query);

  if ($id_res) {
    $found_accounts = mysqli_fetch_all($id_res, MYSQLI_ASSOC);
  } else {
    $status_text = "Database error: " . mysqli_error($db_link);
  }
}
?>

<!DOCTYPE html>
  <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Portal Login - Unsafe Version</title>
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
        <h2>Test Portal (Vulnerable)</h2>

        <form method="POST" action="login_unsafe.php">
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
        <p>Test link: <a href="login_unsafe.php?acc_id=1">Load Account #1</a></p>

        <?php if (!empty($found_accounts)): ?>
          <pre><?php print_r($found_accounts); ?></pre>
        <?php endif; ?>
      </div>
    </body>
  </html>