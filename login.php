<?php
      session_start();

    if (isset($_POST['tlogin'])) {



        require_once ("crypto/token.php");
        include_once ("config.php");

        $login = $_POST['tlogin'];
        $rand_num = hash('sha256', $_POST['rnd'] . ":" . $_SESSION[$login]);

        $ses_r = $_SESSION[$login];
          $_SESSION[$login] = "";
        $r_ecp = substr($_POST['user_sign'], 0, 64);
        $s_ecp = substr($_POST['user_sign'], 64);

        $query = pg_query("SELECT user_id, user_xkey, user_ykey
                                FROM users
                               WHERE user_login='".pg_escape_string($conn, $login)."'
                               LIMIT 1"); 

        $my_count = pg_num_rows($query);

        if ( !$my_count ) {

            $message = "Пользователь <strong>" . $login . "</strong> не зарегистрирован!";
            $status = "poor";

        } else {

            $data = pg_fetch_assoc($query);

            $x_pkey = $data['user_xkey'];
            $y_pkey = $data['user_ykey'];

            $client_get = "<p>Получено клиентом: <br> <span class='logs'>" . $ses_r . "</span></p>";
            $client_out = "<p>Отправлено клиентом: <br><span class='logs'>" . print_r($_POST, true) . "</span></p>";

            $verify = token_verify($rand_num, $x_pkey, $y_pkey, $r_ecp, $s_ecp);

            if ( $verify ) {

              $message = "Пользователь <strong>" . $login . "</strong> авторизован с использованием Рутокен Web!";
              $status = "good";
              $_SESSION['key'] = 1;
              header("Location: index.php");


            } else {

              $message = "Доступ запрещен!";
              $status = "poor";

            }

        }

    }

?>
<!DOCTYPE html>
<html>
<head>
    <title>Авторизация</title>
     <script src="es6promise.js"></script> <!-- // Реализация механизма Promises -->
     <script src="rutokenweb.js"></script> <!-- Загрузка плагина и обертка для работы нового плагина со старыми интерфейсами --> 
     <link rel="stylesheet" href="/css/layout.css" />
    <link rel="stylesheet" href="/css/styles.css" />
    <script src="/script/jquery.js"></script>
    <script src="/script/jquery_custom.js"></script>
    <script src="/script/sha256.js"></script>
    <script src="/script/utf8.js"></script>
    <script src="/script/base64.js"></script>
</head>
<body onload="token_refresh()">
<script type="text/javascript">
  
    window.onload = function () {
 
    rutokenweb.ready.then(function () {
        if (window.chrome) {
            // Проверяем адаптер
            return rutokenweb.isExtensionInstalled();

        } else {
            return Promise.resolve(true);
        }
    }).then(function (result) {
        if (result) {
            // Проверяем плагин
            return rutokenweb.isPluginInstalled();
        } else {
            throw "Установите расширение для браузера <a href='https://chrome.google.com/webstore/detail/%D0%B0%D0%B4%D0%B0%D0%BF%D1%82%D0%B5%D1%80-%D1%80%D1%83%D1%82%D0%BE%D0%BA%D0%B5%D0%BD-web-%D0%BF%D0%BB%D0%B0%D0%B3%D0%B8/boabkkhbickbpleplbghkjpcoebckgai' target='_blank'><span>Адаптер Рутокен Web Плагин</span></a>";
        }
    }).then(function (result) {
        if (result) {
            // Загружаем плагин
            return rutokenweb.loadPlugin();
        } else {
            throw "Установите Рутокен Web Плагин";
        }
    }).then(function (pluginPromised) {
        //Плагин загружен начинать работать с ним
        token_test(pluginPromised);
    }).then(undefined, function (reason) {
        // все ошибки инициализации плагина пробрасываются сюда
        console.log(reason);
    });
}
 
 
function token_test(plugin) {
    plugin.get_version().then(function (pluginVersion) {
        // Promise get_version версия установленного плагина равна pluginVersion
        console.log("Версия плагина: " + pluginVersion);
        return plugin.rtwIsTokenPresentAndOK();
    }).then(function (isTokenPresent){
        // Promise rtwIsTokenPresentAndOK
        if(isTokenPresent === true){
            // Токен найден
            return plugin.rtwGetDeviceID();
        } else{
            // Токен отсутсвует выбрасываем исключение
            throw "Подключите USB-токен!";
        }
    }).then(function (deviceID) {
        // Promise rtwGetDeviceID найденный токен имеет ID deviceID
        console.log("Найден Рутокен Web, ID=" + deviceID);
    }).then(undefined, function (reason) {
        // Обработка ошибок в последнем звене Promises
        console.log(reason);
    });
}
  
</script>

<div class="form thin">
            <form method="post" id="logform">
              <input type="hidden" name="user_sign"  id="user_sign" value="" />
              <input type="hidden" name="tlogin" id="token_log" value="" />
              <input type="hidden" name="rnd" id="rnd_client" value="" />
              <table>
                <tbody>
                  <tr>
                    <th><label>Логин пользователя</label></th>
                    <td>
                      <select  tabindex="1" name="list_log" id="token_login">
                          <option selected="selected" value="none"> — </option>
                      </select>
                    </td>
                  </tr>
                  <?php if ( $message ) { $res  = "
                  <tr>
                    <th>&nbsp;</th>
                    <td class='" . $status . "'>" . $message . "</td>
                </tr> "; } echo $res; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th>&nbsp;</th>
                    <td>
                      <button class='refresh' tabindex="3" type="button" onclick="token_refresh()">Обновить</button>
                      <button tabindex="2" type="button" onclick="rndGet()">Войти</button>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </form>
          </div>
            <object id="cryptoPlugin" type="application/x-rutoken" width="0" height="0">
            <param name="onload" value="pluginit" />
          </object>


<script>
var plugin;
var http = createObject();
var random_text = "";
var current_user = "";

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

function rndReply() {
    if (http.readyState == 4) {
        if (http.readyState == 4) {
            random_text = http.responseText;
            token_sign();
        }
    }
}

function rndGet() {
  random_text = '';
    ltlog = document.getElementById('token_login');
    logstr = ltlog.value;
    if (logstr == "none") {
        alert("Выберите учетную запись на USB-токене.");
    } else {
        tlog = document.getElementById('token_log');
        tlog.value = logstr.substr(0, logstr.indexOf("#%#"));
        if ((current_user == tlog.value) && (random_text != "")) {
            token_sign();
        } else {
            current_user = tlog.value;
            nocache = Math.random();
            http.open('get', 'random.php?tlogin='+encodeURI(tlog.value)+'&nocache='+nocache);
            http.onreadystatechange = rndReply;
            http.send(null);
        }
    }
}

function createObject() {
    var request_type;
    var browser = navigator.appName;
    if(browser == "Microsoft Internet Explorer") {
        request_type = new ActiveXObject("Microsoft.XMLHTTP");
    } else {
        request_type = new XMLHttpRequest();
    }
    return request_type;
}

function token_sign() {
    if ( !plugin.valid ) {
        alert("Не установлен плагин для работы с USB-токеном");
        return;
    }
    clrnd = document.getElementById('rnd_client');
    rd = Math.random().toString(16);
    clrnd.value = Sha256.hash(rd);
    random_text = Sha256.hash(clrnd.value + ':'+ random_text);
    tsign = document.getElementById('user_sign');
    ltlog = document.getElementById('token_login');
    res = plugin.rtwSign(ltlog.value, random_text);
    if ( res != -7 && res != -12 ) {
        if  (res < 0) {
            alert(err[res]);
        } else {
            tsign.value = res;
            tform = document.getElementById('logform');
            tform.submit();
        }
    }
}

function token_refresh() {
    plugin = document.getElementById("cryptoPlugin");
    log_list = document.getElementById("token_login");
    for (var i = log_list.options.length - 1; i >= 0; i--) {
    log_list.remove(i);
    }
    if (!plugin.valid) {
        alert("Не установлен плагин для работы с USB-токеном");
        return;
    }
    if (plugin.rtwIsTokenPresentAndOK() === true) {
        count_cont = plugin.rtwGetNumberOfContainers();
        for( i=0; i < count_cont; i++ ) {
            cont_name = plugin.rtwGetContainerName(i);
            addOption(log_list, cont_name.replace("#%#", " - "), cont_name, 0, 0);
        }
    } else {
        alert("USB-токен отсутствует");
    }
}

function addOption (oListbox, text, value, isDefaultSelected, isSelected) {
  var oOption = document.createElement("option");
  oOption.appendChild(document.createTextNode(text));
  oOption.setAttribute("value", value);
  if (isDefaultSelected) oOption.defaultSelected = true;
  else if (isSelected) oOption.selected = true;
  oListbox.appendChild(oOption);
}


</script>
</body>
</html>