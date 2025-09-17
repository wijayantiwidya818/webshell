<?php
/**
 * index_with_login_md5.php
 *
 * Versi ini menggunakan verifikasi MD5 (TIDAK DIREKOMENDASIKAN).
 * Password MD5 telah dimasukkan sesuai permintaan.
 * Gunakan hanya pada server milikmu sendiri.
 *
 * PENTING: MD5 lemah dan mudah dipatahkan. Disarankan untuk beralih ke password_hash/password_verify.
 */

/* ----------------- CONFIG ----------------- */
$USERNAME = 'admin';

// MD5 hash yang diminta (jangan tempatkan password mentah di sini)
$USE_MD5 = true;
$PASSWORD_HASH_MD5 = '0e96b40d579f8b6d1b43c23b3ead93cf'; // <-- nilai MD5 yang kamu berikan

// Jika nanti ingin kembali ke password_hash yang lebih aman, set $USE_MD5 = false dan isi $PASSWORD_HASH
$PASSWORD_HASH = '';
/* ------------------------------------------ */

session_start();

$err = '';
// logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Jika permintaan login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $pw = isset($_POST['pw']) ? (string)$_POST['pw'] : '';
    $user = isset($_POST['user']) ? (string)$_POST['user'] : '';

    // periksa username sederhana
    if ($user !== $USERNAME) {
        $err = 'Invalid username or password.';
    } else {
        if ($USE_MD5 === true) {
            // ---------- MD5 verification (LEGACY) ----------
            if ($PASSWORD_HASH_MD5 === '') {
                $err = 'MD5 password hash not configured on server.';
            } else {
                if (hash_equals($PASSWORD_HASH_MD5, md5($pw))) {
                    $_SESSION['is_admin'] = true;
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                } else {
                    usleep(300000);
                    $err = 'Invalid username or password.';
                }
            }
        } else {
            // ---------- Secure verification using password_verify ----------
            if (strpos($PASSWORD_HASH, 'REPLACE_ME') !== false || empty($PASSWORD_HASH)) {
                $err = 'Admin: password hash belum dikonfigurasi di server. Ikuti instruksi di bagian atas file.';
            } else {
                if (password_verify($pw, $PASSWORD_HASH)) {
                    $_SESSION['is_admin'] = true;
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                } else {
                    usleep(300000);
                    $err = 'Invalid username or password.';
                }
            }
        }
    }
}

// Jika sudah login, tampilkan area admin sederhana
if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    // Area admin sederhana (ubah sesuai kebutuhan)
    echo "<!doctype html><html lang='id'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Admin Area</title></head><body>";
    echo "<h1>Admin area</h1>";
    echo "<p>Anda sudah login sebagai <strong>".htmlspecialchars($USERNAME)."</strong>.</p>";
    echo "<p><a href='?logout=1'>Logout</a></p>";
    echo "</body></html>";
    exit;
}

// Tampilkan halaman 403 + login form
http_response_code(403);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>403 Forbidden</title>
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;color:#222;background:#f5f7fa;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
    .card{background:white;padding:28px;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,.08);max-width:760px;width:92%}
    h1{margin:0;font-size:28px;color:#c0392b}
    p.lead{margin:10px 0 18px;color:#333}
    .controls{display:flex;gap:12px;align-items:center;margin-top:14px}
    a.button{display:inline-block;padding:8px 12px;border-radius:6px;background:#2d8cff;color:white;text-decoration:none;font-weight:600}
    form.login{margin-top:14px;display:none}
    form.login.visible{display:block}
    input[type="text"], input[type="password"]{width:100%;padding:10px;border-radius:6px;border:1px solid #ddd;font-size:15px;margin-top:6px}
    .note{font-size:13px;color:#666;margin-top:8px}
    .err{color:#a94442;margin-top:10px}
  </style>
</head>
<body>
  <div class="card" role="main" aria-labelledby="t">
    <h1 id="t">403 Forbidden</h1>
    <p class="lead">Access to this resource on the server is denied!</p>

    <div class="controls">
      <a href="#" id="showLogin" class="button" role="button">Admin Login</a>
      <div class="note">Jika Anda admin, klik tombol "Admin Login" untuk masuk.</div>
    </div>

    <form method="post" class="login" id="loginForm" autocomplete="off">
      <input type="hidden" name="action" value="login">
      <label for="user">Username</label>
      <input id="user" name="user" type="text" value="admin" required />
      <label for="pw">Password</label>
      <input id="pw" name="pw" type="password" autocomplete="new-password" required />
      <div style="margin-top:10px;">
        <button type="submit" style="padding:10px 14px;border-radius:6px;border:0;background:#2d8cff;color:white;font-weight:600;cursor:pointer">Masuk</button>
      </div>
      <?php if ($err): ?>
        <div class="err"><?php echo htmlspecialchars($err); ?></div>
      <?php endif; ?>
    </form>

    <div style="margin-top:14px;font-size:13px;color:#666">
      <strong>Keamanan:</strong> Versi ini menggunakan MD5 (legacy). Disarankan untuk beralih ke password_hash/password_verify.
    </div>
  </div>

<script>
(function(){
  var btn = document.getElementById('showLogin');
  var form = document.getElementById('loginForm');

  btn.addEventListener('click', function(e){
    e.preventDefault();
    form.classList.toggle('visible');
    if (form.classList.contains('visible')){
      setTimeout(function(){ document.getElementById('pw').focus(); }, 50);
    }
  });

  // juga tampilkan form saat tekan Tab untuk aksesibilitas (tidak menyembunyikan/memperdaya)
  document.addEventListener('keydown', function(e){
    if (e.key === 'Tab') {
      if (!form.classList.contains('visible')) {
        form.classList.add('visible');
      }
    }
  });
})();
</script>
</body>
</html>
