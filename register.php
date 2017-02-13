<?

if (isset($_POST['login'])) {

    include_once ("config.php");
    $login = $_POST['login'];

    $user_xkey = $_POST['user_xkey'];
    $user_ykey = $_POST['user_ykey'];
    $r_xkey = $_POST['repair_xkey'];
    $r_ykey = $_POST['repair_ykey'];

    if ( $user_xkey && $user_ykey && $r_xkey && $r_ykey ) {


        pg_query($conn,"INSERT INTO users (user_login, user_xkey, user_ykey, r_xkey, r_ykey) VALUES ('$login', '$user_xkey', '$user_ykey', '$r_xkey', '$r_ykey')");
        /*mysql_query("INSERT INTO sample_users SET user_login='".$login."', user_xkey='".$user_xkey."', user_ykey='".$user_ykey."', r_xkey='".$r_xkey."', r_ykey='".$r_ykey."'");*/
        header("Location: login.php");
        exit();

    } else {

        header("Location: register.php");
        exit();
    }
}

?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>Демо-площадка Рутокен: Рутокен Web и PHP</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="/css/layout.css" />
    <link rel="stylesheet" href="/css/styles.css" />
    <script src="/script/jquery.js"></script>
    <script src="/script/jquery_custom.js"></script>
    <script src="/script/sha256.js"></script>
    <script src="/script/utf8.js"></script>
    <script src="/script/base64.js"></script>
    <!--[if lt IE 9]>
      <script src="/script/spike_html5.js"></script>
    <![endif]-->
    <!--[if lt IE 8]>
      <script src="/script/spike_iefix.js"></script>
      <link rel="stylesheet" href="/css/styles_for_IE7.css" />
    <![endif]-->
  </head>
  <body>
    <section class="jump">
      <nav>
        <ul>
          <li class="current"><a href="index.php">Демонстрация</a></li>
          <li><a href="api_methods.php">API плагина Рутокен Web</a> </li>
          <li><a href="http://www.rutoken.ru/support/download/rutoken-web/">Загрузить</a></li>
        </ul>
      </nav>
    </section>

    <section class="content">
      <div class="wrap">
        <article>
          <hgroup>
            <h1>Регистрация</h1>
          </hgroup>
          <!-- Content -->

          <div class="text">
            <p>Для регистрации выберите логин и нажмите кнопку «Зарегистрироваться».<br> Логин пользователя должен состоять из цифр и букв латинского алфавита (любого регистра), а также иметь длину не менее трех и не более тридцати символов.</p>
          </div>

          <div class="form thin">
            <form method="post" id="regform">
              <input type="hidden" name="user_xkey"  id="usr_xkey" value="" />
              <input type="hidden" name="user_ykey"  id="usr_ykey" value="" />
              <input type="hidden" name="repair_xkey"  id="r_xkey" value="" />
              <input type="hidden" name="repair_ykey"  id="r_ykey" value="" />
              <table>
                <tbody>
                  <tr>
                    <th><label>Логин пользователя</label></th>
                    <td><input type="text" name="login" id="user_login" /></td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <th>&nbsp;</th>
                    <td><button type="submit" onclick="chklogin()">Зарегистрироваться</button></td>
                  </tr>
                </tfoot>
              </table>
            </form>
          </div>

          <div class='slider detail'>
            <dl>
              <dt><h2><span>Справочная информация</span></h2></dt>
              <dd>
                <p>В случае успешной регистрации, будет сгенерирована ключевая пара, предназначенная для защищенной аутентификации пользователя c логином <strong>Login</strong> на Web-сайте <strong>SiteName</strong>.</p>
                <p>Ключевая пара <strong>(e_(Login, SiteName),d_(Login, SiteName))</strong>, где:</p>
                <ul>
                  <li>Открытый ключ <strong>e_(Login, SiteName)</strong> — хранится на USB-токене, передается на Web-сайт и привязывается к логину пользователя;</li>
                  <li>Закрытый ключ <strong>d_(Login, SiteName)</strong> — хранится на USB-токене и не может быть извлечен из памяти USB-токена.</li>
                </ul>
                <p>Таким образом, на сервере сохраняется пара <strong>(Login, e_(Login, SiteName))</strong>. Пользователь может повторно пройти процедуру регистрации на Web-сайте, но уже с другим логином. Количество повторных регистраций на одном и том же Web-сайте ограничено только объемом доступной памяти USB-токена, предназначенной для хранения контейнеров.</p>
              </dd>
            </dl>
          </div>

          <object id="cryptoPlugin" type="application/x-rutoken" width="0" height="0">
            <param name="onload" value="pluginit" />
          </object>

<script>
var plugin;
var http = createObject();
var error_text = "";

var err = [];
    err[-1]  = 'USB-токен не найден';
    err[-2]  = 'USB-токен не залогинен пользователем';
    err[-3]  = 'PIN-код не верен';
    err[-4]  = 'PIN-код не корректен';
    err[-5]  = 'PIN-код заблокирован';
    err[-6]  = 'Неправильная длина PIN-кода';
    err[-7]  = 'Отказ от ввода PIN-кода';
    err[-10] = 'Неправильные аргументы функции';
    err[-11] = 'Неправильная длина аргументов функции';
    err[-12] = 'Открыто другое окно ввода PIN-кода';
    err[-20] = 'Контейнер не найден';
    err[-21] = 'Контейнер уже существует';
    err[-22] = 'Контейнер поврежден';
    err[-30] = 'ЭЦП не верна';
    err[-40] = 'Не хватает свободной памяти чтобы завершить операцию';
    err[-50] = 'Библиотека не загружена';
    err[-51] = 'Библиотека находится в неинициализированном состоянии';
    err[-52] = 'Библиотека не поддерживает расширенный интерфейс';
    err[-53] = 'Ошибка в библиотеке rtpkcs11ecp';

function createObject() {
  var request_type;
  var browser = navigator.appName;
  if (browser == "Microsoft Internet Explorer") {
    request_type = new ActiveXObject("Microsoft.XMLHTTP");
  } else {
    request_type = new XMLHttpRequest();
  }
  return request_type;
}

function errorReply() {
  if (http.readyState == 4) {
    if (http.responseText.indexOf("valid") > 0) {
      link_token();
    } else {
      alert(http.responseText);
    }
  }
}

function chklogin() {
  ulogin = document.getElementById('user_login');
  nocache = Math.random();
  http.open('get', 'validation.php?tlogin='+encodeURI(ulogin.value)+'&nocache='+nocache);
  http.onreadystatechange = errorReply;
  http.send(null);
}

function link_token() {
  plugin = document.getElementById("cryptoPlugin");
  xkey = document.getElementById('usr_xkey');
  ykey = document.getElementById('usr_ykey');
  rxkey = document.getElementById('r_xkey');
  rykey = document.getElementById('r_ykey');
  ulogin = document.getElementById('user_login');
  if (ulogin.value == '') {
    inf.innerHTML = "Введите логин";
    return;
  }
  if (!plugin.valid) {
    inf.innerHTML = "Установите плагин";
    return;
  }
  if ( !(plugin.rtwIsTokenPresentAndOK() === true) ) {
    alert("USB-токен отсутствует!");
  }
  if (!plugin.rtwIsUserLoggedIn()) {
    plugin.rtwUserLoginDlg();
  }
  if (plugin.rtwIsUserLoggedIn()) {
    key = plugin.rtwGenKeyPair(ulogin.value + '#%#<?php echo getenv("HTTP_HOST"); ?>');
    rkey = plugin.rtwGetPublicKey('repair key');
    plugin.rtwLogout();
    if (key) {
      xkey.value = key.substring(0,64);
      ykey.value = key.substring(64);
      if (rkey) {
        rxkey.value = rkey.substring(0,64);
        rykey.value = rkey.substring(64);
      }
      tform = document.getElementById('regform');
      tform.submit();
    } else {
      alert("Ошибка при создании ключевой пары.");
    }
  } else {
    alert("Введен ошибочный PIN-код.");
  }
}

function pluginit() {
// ...
}
</script>

          <!-- /Content -->
        </article>
      </div>
      <nav>
        <ul>
          <li class="current"><a href="register.php"><span>Регистрация</span></a></li>
          <li><a href="login.php"><span>Авторизация</span></a></li>
          <li><a href="recovery.php"><span>Восстановление доступа</span></a></li>
          <li><a href="signform.php"><span>Web-формы</span></a></li>
        </ul>
      </nav>
    </section>

    <footer class="footer">
      <dl>
        <dt>1994 — 2013 © <a href="http://www.aktiv-company.ru/" target="_blank"><span>Компания «Актив»</span></a></dt>
        <dd><a href="http://www.rutoken.ru/" target="_blank"><span>www.rutoken.ru</span></a></dd>
      </dl>
    </footer>
  </body>
</html>