<?php
session_start();

$USERNAME = 'admin';

// --- SETUP: masukkan hash yang dihasilkan sendiri di sini ---
// Contoh (bukan nilai nyata — ganti dengan nilai dari perintah password_hash):
$PASSWORD_HASH = '9b26705ac5dfe98fd6431bd04cd8e7d5e8cda25912768e0fd646c660396440b1';

// ----------------------------------------------------------------

// Jika kamu belum memasukkan hash di atas, beri pesan agar admin membuat hash
if (strpos($PASSWORD_HASH, 'PUT_HASH_HERE') !== false) {
    // Untuk keamanan: jangan biarkan login aktif sebelum hash diisi
    $login_enabled = false;
} else {
    $login_enabled = true;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $login_enabled) {
    // Ambil password dari form
    $pw = isset($_POST['pw']) ? (string)$_POST['pw'] : '';

    // Verifikasi
    if (password_verify($pw, $PASSWORD_HASH)) {
        // login sukses: set session
        $_SESSION['is_admin'] = true;
        // redirect ke area admin atau halaman yang diinginkan
        header('Location: /'); // ubah target sesuai kebutuhan
        exit;
    } else {
        // delay kecil untuk mengurangi brute-force
        usleep(300000);
        $err = 'Login failed';
    }
}

// Jika sudah login, kamu bisa tampilkan sesuatu atau redirect
if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    echo "<h2>Logged in (admin)</h2>";
    echo "<p><a href=\"?logout=1\">Logout</a></p>";
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
        exit;
    }
    exit;
}

// Tampilkan halaman 403 + login form (tampilan)
http_response_code(403);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>403 Forbidden</title>
<style>
  body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;color:#222;background:#f8f8f8;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
  .box{background:white;padding:28px;border-radius:8px;box-shadow:0 6px 30px rgba(0,0,0,.08);max-width:720px;width:90%;text-align:center}
  h1{margin:0;font-size:28px;color:#c0392b}
  p.lead{margin:10px 0 22px;color:#333}
  /* area yang tampak seperti 403 message */
  .fake-403{padding:18px;border-radius:6px;background:#fff5f5;border:1px solid #ffd7d7;margin-bottom:18px}
  /* form area (hidden secara visual) */
  .login-area{height:0;overflow:visible}
  input[type="password"]{
    width:100%;
    padding:10px 12px;
    margin-top:8px;
    border-radius:6px;
    border:1px solid #ddd;
    font-size:16px;
    transition: all .18s ease;
    /* default: kita sembunyikan secara visual tanpa menghapus dari DOM */
    opacity:0;
    transform: translateY(-6px) scale(.98);
    height:0;
    pointer-events:none;
  }
  input[type="password"].visible{
    opacity:1;
    transform:none;
    height:44px;
    pointer-events:auto;
  }
  .hint{font-size:13px;color:#666;margin-top:8px}
  .err{color:#a94442;margin-top:8px}
  .show-controls{margin-top:12px;font-size:13px;color:#555}
  button.btn{margin-top:12px;padding:10px 14px;border-radius:6px;border:0;background:#2d8cff;color:white;font-weight:600;cursor:pointer}
</style>
</head>
<body>
  <div class="box" role="main">
    <div class="fake-403">
      <h1>403 Forbidden</h1>
      <p class="lead">Access to this resource on the server is denied!</p>
      <p class="hint">Jika Anda adalah administrator, tekan <kbd>Tab</kbd> untuk menampilkan kotak login.</p>
    </div>

    <?php if (!$login_enabled): ?>
      <div class="err">Administrator: password hash belum terpasang. Buat hash pakai password_hash() dan tempel di file ini.</div>
    <?php else: ?>
      <form class="login-area" method="post" id="loginForm" autocomplete="off" onsubmit="return true;">
        <!-- field password tersembunyi secara visual: -->
        <input id="pw" name="pw" type="password" aria-label="Administrator password" />
        <div>
          <button id="submitBtn" class="btn" type="submit" style="display:none">Masuk</button>
        </div>
        <?php if ($err): ?>
          <div class="err"><?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>
      </form>
      <div class="show-controls">
        <small>Tip: tekan <kbd>Tab</kbd> untuk fokus ke kotak password, atau klik area ini.</small>
      </div>
    <?php endif; ?>
  </div>

<script>
(function(){
  var pw = document.getElementById('pw');
  var submitBtn = document.getElementById('submitBtn');
  var loginForm = document.getElementById('loginForm');

  // Jika pengguna menekan Tab pada halaman, fokuskan ke input password dan tampilkan
  document.addEventListener('keydown', function(e){
    if (e.key === 'Tab') {
      // tampilkan
      pw.classList.add('visible');
      // sedikit delay lalu fokus
      setTimeout(function(){ pw.focus(); }, 10);
    }
  });

  // Jika pengguna mengklik area .show-controls, juga tampilkan dan fokus
  document.querySelector('.show-controls').addEventListener('click', function(){
    pw.classList.add('visible');
    setTimeout(function(){ pw.focus(); }, 10);
  });

  // Jika input fokus, tampilkan tombol submit
  pw.addEventListener('focus', function(){ submitBtn.style.display = 'inline-block'; });
  pw.addEventListener('blur', function(){ /* optional: hide again after blur */ });

  // Optional: prevent autocomplete by browsers
  pw.setAttribute('autocomplete','new-password');

  // Progressive enhancement: jika JavaScript mati, tidak akan muncul input — but that's OK
})();
</script>
</body>
</html>
