<?php
session_start();
require __DIR__ . '/db.php'; // gyökérben lévő db.php

// Lekérjük a legutolsó kérdés qid-jét a navigációs sávhoz
$qid = 1; // alapértelmezett
$latest = $conn->query("SELECT qid FROM question ORDER BY qid DESC LIMIT 1");
if ($latest && $latest->num_rows > 0) {
  $qid = $latest->fetch_assoc()['qid'];
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Szavazás kezdőlap</title>
  <style>
    .notice {
      font-size: 1.2em;
      color: #b00;
      margin-left: 10px;
    }
    nav {
      background:#eee;
      padding:10px;
      margin-bottom:20px;
    }
    li {
      margin-bottom: 8px;
    }
  </style>
</head>
<body>
  <div id="userbox">
    <?php if (!isset($_SESSION['uid'])): ?>
      <!-- Vendég felhasználó -->
      <a href="login.html">Bejelentkezés</a>
    <?php else: ?>
      <!-- Bejelentkezett felhasználó -->
      Bejelentkezve: <strong><?= htmlspecialchars($_SESSION['name']) ?></strong>
      <form action="api/logout.php" method="post" style="display:inline;">
        <button type="submit">Kijelentkezés</button>
      </form>
    <?php endif; ?>
  </div>

  <nav>
    <a href="index.php">Kezdőlap</a>
    <?php if (isset($_SESSION['uid'])): ?>
      | <a href="poll.html?qid=<?= $qid ?>">Szavazás</a>
      | <a href="result.html?qid=<?= $qid ?>">Eredmények</a>
      | <a href="admin.html">Új kérdés</a>
      | <a href="dashboard.html">Kérdéskezelés</a>
    <?php endif; ?>
  </nav>

  <h1>Elérhető kérdések</h1>
  <ul>
    <?php
    $res = $conn->query("SELECT qid, qtext FROM question ORDER BY qid ASC");
    if ($res && $res->num_rows > 0) {
      while ($row = $res->fetch_assoc()) {
        echo "<li>";
        echo htmlspecialchars($row['qtext']);
        if (isset($_SESSION['uid'])) {
          echo " <a href=\"poll.html?qid={$row['qid']}\">[Szavazás]</a>";
          echo " <a href=\"result.html?qid={$row['qid']}\">[Eredmények]</a>";
        } else {
          echo " <span class=\"notice\">(Bejelentkezés szükséges)</span>";
        }
        echo "</li>";
      }
    } else {
      echo "<li>Nincs elérhető kérdés</li>";
    }
    $conn->close();
    ?>
  </ul>
</body>
</html>