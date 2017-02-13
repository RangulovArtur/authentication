Function.prototype.bind = function (scope) {
    var fn = this;
    return function () {
        return fn.apply(scope, arguments);
    };
};


var rutokenwebPlugin = new (function () {

    var methods = [
    'get_version',
        'rtwIsTokenPresentAndOK',
        'rtwGetDeviceID',
        'rtwGetNumberOfContainers',
        'rtwGenKeyPair',
        'rtwGetContainerName',
        'rtwGetPublicKey',
        'rtwDestroyContainer',
        'rtwSign',
        'rtwHashSign',
        'rtwMakeSessionKey',
        'rtwRepair',
        'rtwUserLoginDlg',
        'rtwLogout',
        'rtwIsUserLoggedIn'
    ];

    var err = [];
    err[true] = "TRUE";
    err[-1] = 'USB-токен не найден';
    err[-2] = 'USB-токен не залогинен пользователем';
    err[-3] = 'PIN-код не верен';
    err[-4] = 'PIN-код не корректен';
    err[-5] = 'PIN-код заблокирован';
    err[-6] = 'Неправильная длина PIN-кода';
    err[-7] = 'Отказ от ввода PIN-кода';
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

    this.getError = function (errorcode) {
        return err[errorcode] ||  errorcode;
    };

    

    this.init = function () {

        var plugin = document.getElementById("testPlugin");
        if (!plugin.valid) {
            testSuit.setError('плагин не установлен');
        }


        // старая версия плагина - недоступны некоторые функции
        var version = plugin.get_version().split('.');
        if (!(version[0] > 0 && version[1] > 3)) {
            testSuit.setOldVersion();
        }


        for (var k = 0; k < methods.length; ++k) {
            var f = methods[k];

            if (!plugin[f]) {
                //  message += "В плагине нет функции: " + f + "<br />";
                testSuit.setDisabled(f);
            } else
                this[f] = plugin[f];
        }

    };

})();

var ui = new (function () {

})();


var testSuit = new (function () {
    var tests = {};
    this.setDisabled = function (funcName) {
        $(tests[funcName].div).addClass('disabled');
    };

    this.setOldVersion = function () {
        this.setError('Вы используете устаревшую версию плагина. В разделе "Загрузки" можно скачать текущую актуальную верисю - 1.4.0.0');
        $('button.version1400').attr('disabled', 'disabled');
    };

    this.setError = function (message) {
        $('#errorlabel').html(message);
    };


    var Win1251ToHEX = function (str) {
        var Win1251 =
                    {
                        0x402: 0x80,
                        0x403: 0x81,
                        0x201A: 0x82,
                        0x453: 0x83,
                        0x201E: 0x84,
                        0x2026: 0x85,
                        0x2020: 0x86,
                        0x2021: 0x87,
                        0x20AC: 0x88,
                        0x2030: 0x89,
                        0x409: 0x8A,
                        0x2039: 0x8B,
                        0x40A: 0x8C,
                        0x40C: 0x8D,
                        0x40B: 0x8E,
                        0x40F: 0x8F,
                        0x452: 0x90,
                        0x2018: 0x91,
                        0x2019: 0x92,
                        0x201C: 0x93,
                        0x201D: 0x94,
                        0x2022: 0x95,
                        0x2013: 0x96,
                        0x2014: 0x97,
                        0x2122: 0x99,
                        0x459: 0x9A,
                        0x203A: 0x9B,
                        0x45A: 0x9C,
                        0x45C: 0x9D,
                        0x45B: 0x9E,
                        0x45F: 0x9F,
                        0xA0: 0xA0,
                        0x40E: 0xA1,
                        0x45E: 0xA2,
                        0x408: 0xA3,
                        0xA4: 0xA4,
                        0x490: 0xA5,
                        0xA6: 0xA6,
                        0xA7: 0xA7,
                        0x401: 0xA8,
                        0xA9: 0xA9,
                        0x404: 0xAA,
                        0xAB: 0xAB,
                        0xAC: 0xAC,
                        0xAD: 0xAD,
                        0xAE: 0xAE,
                        0x407: 0xAF,
                        0xB0: 0xB0,
                        0xB1: 0xB1,
                        0x406: 0xB2,
                        0x456: 0xB3,
                        0x491: 0xB4,
                        0xB5: 0xB5,
                        0xB6: 0xB6,
                        0xB7: 0xB7,
                        0x451: 0xB8,
                        0x2116: 0xB9,
                        0x454: 0xBA,
                        0xBB: 0xBB,
                        0x458: 0xBC,
                        0x405: 0xBD,
                        0x455: 0xBE,
                        0x457: 0xBF,
                        0x410: 0xC0,
                        0x411: 0xC1,
                        0x412: 0xC2,
                        0x413: 0xC3,
                        0x414: 0xC4,
                        0x415: 0xC5,
                        0x416: 0xC6,
                        0x417: 0xC7,
                        0x418: 0xC8,
                        0x419: 0xC9,
                        0x41A: 0xCA,
                        0x41B: 0xCB,
                        0x41C: 0xCC,
                        0x41D: 0xCD,
                        0x41E: 0xCE,
                        0x41F: 0xCF,
                        0x420: 0xD0,
                        0x421: 0xD1,
                        0x422: 0xD2,
                        0x423: 0xD3,
                        0x424: 0xD4,
                        0x425: 0xD5,
                        0x426: 0xD6,
                        0x427: 0xD7,
                        0x428: 0xD8,
                        0x429: 0xD9,
                        0x42A: 0xDA,
                        0x42B: 0xDB,
                        0x42C: 0xDC,
                        0x42D: 0xDD,
                        0x42E: 0xDE,
                        0x42F: 0xDF,
                        0x430: 0xE0,
                        0x431: 0xE1,
                        0x432: 0xE2,
                        0x433: 0xE3,
                        0x434: 0xE4,
                        0x435: 0xE5,
                        0x436: 0xE6,
                        0x437: 0xE7,
                        0x438: 0xE8,
                        0x439: 0xE9,
                        0x43A: 0xEA,
                        0x43B: 0xEB,
                        0x43C: 0xEC,
                        0x43D: 0xED,
                        0x43E: 0xEE,
                        0x43F: 0xEF,
                        0x440: 0xF0,
                        0x441: 0xF1,
                        0x442: 0xF2,
                        0x443: 0xF3,
                        0x444: 0xF4,
                        0x445: 0xF5,
                        0x446: 0xF6,
                        0x447: 0xF7,
                        0x448: 0xF8,
                        0x449: 0xF9,
                        0x44A: 0xFA,
                        0x44B: 0xFB,
                        0x44C: 0xFC,
                        0x44D: 0xFD,
                        0x44E: 0xFE,
                        0x44F: 0xFF
                    };
        var o1, o2, c, coded;
        coded = '';
        for (c = 0; c < str.length; c++) {
            o2 = str.charCodeAt(c);
            o1 = o2 < 128 ? o2 : Win1251[o2];
            if (o1 == null && o2 > 0)
                o1 = 63; // if can't be decoded, put '?'
            coded += ((o1 & 0xf0) >> 4).toString(16);
            o1 = o1 & 0x0f;
            coded += o1.toString(16);
        }
        return coded;
    };



    this.init = function () {
        // для неподдерживаемых функций
        $('.accent.api').each(function () {
            var func = $(this).data('func');
            tests[func] = { name: func, div: this };
        });

        $('button[data-test]').each(function () {
            var btn = $(this);
            var func = btn.data('test');
            btn.click(function () {
                showTest(func, btn);

                return false;
            });
            btn.after('<div class="source"><pre class="brush: js; toolbar: false;">' + testSuit[func].toString() + '</pre></div>');
            btn.after('<b class="viewer"><span>Код примера</span></b>');
        });

        $('b.viewer').click(function () {
            $(this).next().slideToggle();
            return false;
        });



    };

    this.showResult = function (resultmessage, btn) {
        if (resultmessage) {
            resultmessage = rutokenwebPlugin.getError(resultmessage);
            btn.parents('div.accent.api').find('div.result').html(resultmessage);
        }
    };


    function showTest(func, btn) {
        testSuit.showResult(testSuit[func](), btn);
    }




this.get_version = function () {
  return rutokenwebPlugin.get_version();
};

this.rtwIsTokenPresentAndOK = function () {
  return rutokenwebPlugin.getError(rutokenwebPlugin.rtwIsTokenPresentAndOK());
};

this.rtwGetDeviceID = function () {
  return rutokenwebPlugin.rtwGetDeviceID();
};

this.rtwGetNumberOfContainers = function () {
  var b = rutokenwebPlugin.rtwGetNumberOfContainers();
  return b;
};

this.rtwGenKeyPair = function () {
  return rutokenwebPlugin.getError(rutokenwebPlugin.rtwGenKeyPair(document.getElementById('cont_name').value));
};

this.rtwGenKeyPair_CSS = function () {
  return rutokenwebPlugin.getError(rutokenwebPlugin.rtwGenKeyPair(document.getElementById('cont_name').value, document.getElementById('qtcss').value));
};

this.rtwGenKeyPair_CSS_CallBack = function () {
  var resultElement = document.getElementById('newpubkey');
  resultElement.innerHTML = 'Ждем ввод пин-кода и результат...';
  rutokenwebPlugin.rtwGenKeyPair(document.getElementById('cont_name').value, document.getElementById('qtcss').value, callback, errorCallback);
  // callback
  function callback(result) {
    resultElement.innerHTML = result;
  }
  /*errorcallback*/
  function errorCallback(error) {
    resultElement.innerHTML = rutokenwebPlugin.getError(error);
  }
};

this.token_refresh = function () {
  var logList = document.getElementById("token_login");
  for (var i = logList.options.length - 1; i >= 0; i--) {
    logList.remove(i);
  }
  var countCont = rutokenwebPlugin.rtwGetNumberOfContainers();
  for (i = 0; i < countCont; i++) {
    var cName = rutokenwebPlugin.rtwGetContainerName(i);
    addOption(logList, cName.replace("#%#", " - "), cName, 0, 0);
  }
  function addOption(oListbox, text, value, isDefaultSelected, isSelected) {
    var oOption = document.createElement("option");
    oOption.appendChild(document.createTextNode(text));
    oOption.setAttribute("value", value);
    if (isDefaultSelected) oOption.defaultSelected = true;
    else if (isSelected) oOption.selected = true;
    oListbox.appendChild(oOption);
  }
};


this.rtwGetPublicKey = function () {
  return 'Public key: ' + rutokenwebPlugin.rtwGetPublicKey(document.getElementById('token_login').value);
};

this.GetRepairKey = function () {
  return 'Repair key: ' + rutokenwebPlugin.rtwGetPublicKey('repair key');
};

this.rtwDestroyContainer = function () {
  return rutokenwebPlugin.rtwDestroyContainer(document.getElementById('token_login').value);
};

this.rtwDestroyContainer_CSS = function () {
  return rutokenwebPlugin.rtwDestroyContainer(document.getElementById('token_login').value, document.getElementById('qtcss').value);
};

this.rtwDestroyContainer_CSS_CallBack = function () {
  var resultElement = document.getElementById('destroyresult');
  resultElement.innerHTML = 'Ждем ввод пин-кода и результат...';
  rutokenwebPlugin.rtwDestroyContainer(document.getElementById('token_login').value, document.getElementById('qtcss').value, destroyCallback, destroyCallbackError);
  function destroyCallback(message) {
    resultElement.innerHTML = message;
  }
  function destroyCallbackError(errorMessage) {
    resultElement.innerHTML = rutokenwebPlugin.getError(errorMessage);
  }
};

this.rtwSign = function () {
  document.getElementById('signresult').innerHTML = '';
  return rutokenwebPlugin.rtwSign(document.getElementById('token_login').value, document.getElementById('hash').value);
};

this.rtwSign_CSS = function () {
  document.getElementById('signresult').innerHTML = '';
  return rutokenwebPlugin.rtwSign(document.getElementById('token_login').value, document.getElementById('hash').value, document.getElementById('qtcss').value);
};

this.rtwSign_CSS_CallBack = function () {
  document.getElementById('signresult').innerHTML = 'Ждем ввод пин-кода и результат...';
  rutokenwebPlugin.rtwSign(document.getElementById('token_login').value, document.getElementById('hash').value, document.getElementById('qtcss').value, signCallback, signCallbackError);
  function signCallback(message) {
    document.getElementById('signresult').innerHTML = 'ЭЦП: <br />' + message;
  }
  function signCallbackError(errorMessage) {
    document.getElementById('signresult').innerHTML = rutokenwebPlugin.getError(errorMessage);
  }
};

this.rtwHashSign = function () {
  document.getElementById('signmessageresult').innerHTML = '';
  return rutokenwebPlugin.rtwHashSign(document.getElementById('token_login').value, Win1251ToHEX(document.getElementById('message').value));
};

this.rtwHashSign_CSS = function () {
  document.getElementById('signmessageresult').innerHTML = '';
  return rutokenwebPlugin.rtwHashSign(document.getElementById('token_login').value, Win1251ToHEX(document.getElementById('message').value), document.getElementById('qtcss').value);
};

this.rtwHashSign_CSS_CallBack = function () {
  document.getElementById('signmessageresult').innerHTML = 'Ждем ввод пин-кода и результат...';
  rutokenwebPlugin.rtwHashSign(document.getElementById('token_login').value, Win1251ToHEX(document.getElementById('message').value), document.getElementById('qtcss').value, signHashCallback, signHashCallbackError);
  function signHashCallback(message) {
    document.getElementById('signmessageresult').innerHTML = 'ЭЦП: <br />' + message;
  }
  function signHashCallbackError(errorMessage) {
    document.getElementById('signmessageresult').innerHTML = rutokenwebPlugin.getError(errorMessage);
  }
};

this.rtwMakeSessionKey = function () {
  document.getElementById('sessionresult').innerHTML = '';
  return  rutokenwebPlugin.rtwMakeSessionKey(document.getElementById('token_login').value, document.getElementById('pubkeysession').value, document.getElementById('ukm').value);
};

this.rtwMakeSessionKey_CSS = function () {
  document.getElementById('sessionresult').innerHTML = '';
  return  rutokenwebPlugin.rtwMakeSessionKey(document.getElementById('token_login').value, document.getElementById('pubkeysession').value, document.getElementById('ukm').value, document.getElementById('qtcss').value);
};

this.rtwMakeSessionKey_CSS_CallBack = function () {
  document.getElementById('sessionresult').innerHTML = 'Ждем ввод пин-кода и результат...';
  rutokenwebPlugin.rtwMakeSessionKey(document.getElementById('token_login').value, document.getElementById('pubkeysession').value, document.getElementById('ukm').value, document.getElementById('qtcss').value, sessionCallback, sessionCallbackError);
  function sessionCallback(message) {
    document.getElementById('sessionresult').innerHTML = 'Сессионный ключ: <br />' + message;
  }
  function sessionCallbackError(errorMessage) {
    document.getElementById('sessionresult').innerHTML =  rutokenwebPlugin.getError(errorMessage);
  }
};

this.rtwRepair = function () {
  return rutokenwebPlugin.rtwRepair(document.getElementById('priv_key').value, document.getElementById('repairhash').value);
};

this.rtwUserLoginDlg = function () {
  return rutokenwebPlugin.rtwUserLoginDlg();
};

this.rtwLogout = function () {
  return rutokenwebPlugin.rtwLogout();
};

this.rtwIsUserLoggedIn = function () {
  return rutokenwebPlugin.rtwIsUserLoggedIn();
};
    

})();


$(document).ready(function () {
    testSuit.init();
    rutokenwebPlugin.init();
    testSuit.token_refresh();
});