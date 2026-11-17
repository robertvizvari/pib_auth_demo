<?php
// simple benchmark script to measure query latency across 1000 iterations
$iterations = 1000;
$test_id = 1;

// connect with mysqli (for variants A and B)
$db_link = mysqli_connect("localhost", "root", "", "pib_portal_db");
if (!$db_link) {
  die("MySQLi connection failed: " . mysqli_connect_error());
}

// connect with PDO using native prepared statements (for variants C1 and C2)
$dsn = "mysql:host=localhost;dbname=pib_portal_db;charset=utf8mb4";
$pdo_conn = new PDO($dsn, "root", "", [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_EMULATE_PREPARES => false
]);

// quick warmup query so initial connection doesnt skew variant 1
mysqli_query($db_link, "SELECT acc_id, login_name FROM accounts WHERE acc_id = 1");

// variant A: raw mysqli_query() with unescaped input (unsafe, but fastest)
$start_a = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
  $res = mysqli_query($db_link, "SELECT acc_id, login_name FROM accounts WHERE acc_id = " . $test_id);
  mysqli_fetch_assoc($res);
}
$time_a = (hrtime(true) - $start_a) / 1e6;

// variant B: mysqli_real_escape_string() + query (safe, but slower than raw query)
$start_b = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
  $safe_id = mysqli_real_escape_string($db_link, (string)$test_id);
  $res = mysqli_query($db_link, "SELECT acc_id, login_name FROM accounts WHERE acc_id = " . $safe_id);
  mysqli_fetch_assoc($res);
}
$time_b = (hrtime(true) - $start_b) / 1e6;

// variant C: PDO prepared statements (safe, but slower than mysqli)
$start_c1 = hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
  $stmt = $pdo_conn->prepare("SELECT acc_id, login_name FROM accounts WHERE acc_id = :id");
  $stmt->bindValue(':id', $test_id, PDO::PARAM_INT);
  $stmt->execute();
  $stmt->fetch(PDO::FETCH_ASSOC);
}
$time_c1 = (hrtime(true) - $start_c1) / 1e6;

// variant C2: PDO prepare() once + 1000 execute() calls
$start_c2 = hrtime(true);
$stmt = $pdo_conn->prepare("SELECT acc_id, login_name FROM accounts WHERE acc_id = :id");
for ($i = 0; $i < $iterations; $i++) {
  $stmt->bindValue(':id', $test_id, PDO::PARAM_INT);
  $stmt->execute();
  $stmt->fetch(PDO::FETCH_ASSOC);
}
$time_c2 = (hrtime(true) - $start_c2) / 1e6;
?>

<!DOCTYPE html>
  <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Query Latency Benchmark (1,000 Iterations)</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f4f4f4; }
        .box { background: #fff; padding: 25px; max-width: 650px; margin: auto; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #eee; }
      </style>
    </head>

    <body>
      <div class="box">
        <h2>Query Execution Benchmark (<?= $iterations ?> Lookups)</h2>
        <table>
            <tr>
              <th>Execution Method</th>
              <th>Total Time (ms)</th>
              <th>Avg per Query (ms)</th>
            </tr>
            <tr>
              <td><strong>Variant A:</strong> Raw <code>mysqli_query()</code></td>
              <td><?= number_format($time_a, 2) ?> ms</td>
              <td><?= number_format($time_a / $iterations, 4) ?> ms</td>
            </tr>
            <tr>
              <td><strong>Variant B:</strong> <code>mysqli_real_escape_string()</code> + query</td>
              <td><?= number_format($time_b, 2) ?> ms</td>
              <td><?= number_format($time_b / $iterations, 4) ?> ms</td>
            </tr>
            <tr>
              <td><strong>Variant C1:</strong> PDO <code>prepare()</code> inside loop</td>
              <td><?= number_format($time_c1, 2) ?> ms</td>
              <td><?= number_format($time_c1 / $iterations, 4) ?> ms</td>
            </tr>
            <tr>
              <td><strong>Variant C2:</strong> PDO <code>prepare()</code> once + 1,000 <code>execute()</code></td>
              <td><?= number_format($time_c2, 2) ?> ms</td>
              <td><?= number_format($time_c2 / $iterations, 4) ?> ms</td>
            </tr>
        </table>
        <p style="margin-top: 15px;"><a href="benchmark_queries.php">Run benchmark again</a></p>
      </div>
    </body>
  </html>