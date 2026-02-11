<?php
session_start();
require __DIR__ . '/db.php';

$qid = 1;
$latest = $conn->query("SELECT qid FROM question ORDER BY qid DESC LIMIT 1");
if ($latest && $latest->num_rows > 0) {
  $qid = (int)$latest->fetch_assoc()['qid'];
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Szavazás kezdőlap</title>
  <link rel="stylesheet" href="app.css" />
</head>
<body>
  <header>
    <h1>Szavazó Rendszer</h1>
    <nav>
      <a href="index.php">Kezdőlap</a>
      <?php if (isset($_SESSION['uid'])): ?>
        <a href="poll.html?qid=<?= $qid ?>">Szavazás</a>
        <a href="result.html?qid=<?= $qid ?>">Eredmények</a>
        <a href="admin.html">Új kérdés</a>
        <a href="dashboard.html">Kérdéskezelés</a>
      <?php else: ?>
        <a href="login.html">Bejelentkezés</a>
      <?php endif; ?>
    </nav>
    <div id="userbox">
      <?php if (!isset($_SESSION['uid'])): ?>
        <a href="login.html">Bejelentkezés</a>
      <?php else: ?>
        Bejelentkezve: <strong><?= htmlspecialchars($_SESSION['name']) ?></strong>
        <form action="api/logout.php" method="post" style="display:inline;">
          <button type="submit">Kijelentkezés</button>
        </form>
      <?php endif; ?>
    </div>
  </header>

  <main>
    <section class="card">
      <h2>Elérhető kérdések</h2>
      <div class="list">
        <?php
        $res = $conn->query("SELECT qid, qtext FROM question ORDER BY qid ASC");
        if ($res && $res->num_rows > 0) {
          while ($row = $res->fetch_assoc()) {
            $questionId = (int)$row['qid'];
            echo '<div class="list-item">';
            echo '<div><strong>' . htmlspecialchars($row['qtext']) . '</strong></div>';
            if (isset($_SESSION['uid'])) {
              echo '<div class="actions">';
              echo '<a href="poll.html?qid=' . $questionId . '">Szavazás</a>';
              echo '<a href="result.html?qid=' . $questionId . '">Eredmények</a>';
              echo '</div>';
            } else {
              echo '<div class="notice">Bejelentkezés szükséges a szavazáshoz.</div>';
            }
            echo '</div>';
          }
        } else {
          echo '<p class="small">Nincs elérhető kérdés.</p>';
        }
        $conn->close();
        ?>
      </div>
    </section>
  </main>
</body>
</html>
