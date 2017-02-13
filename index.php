<?php require 'con_new.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <link rel="stylesheet" href="style/style.css">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
     <link href="http://allfont.ru/allfont.css?fonts=pt-sans" rel="stylesheet" type="text/css" />
    <script src="script/jquery-3.1.0.min.js"></script>
    <script src="script/scripts.js"></script>
    <script src="script/es6promise.js"></script> <!-- // Реализация механизма Promises -->
     <script src="script/rutokenweb.js"></script> <!-- Загрузка плагина и обертка для работы нового плагина со старыми интерфейсами --> 
    <title>Главная страница</title>
</head>
<body>
    <div>
    <form method="get" action="/search" target="_blank">
   <p><input type="search" name="q" placeholder="Поиск по сайту"> 
   <input type="submit" value="Найти"></p>
  </form>
  </div>
<?php if(isset($_SESSION['key'])) {
        echo "<a class='out'>ВЫХОД</a>";}
      if (!isset($_SESSION['key'])){ ?>
<div class="authorization">
<p>Для работы с базой данных, пожалуйста, авторизуйтесь</p>
<form action="login.php">
    <button type="submit" class="">Авторизоваться</button>
</form>
</div>
<? } else { ?>
    <div class="wrapper">
        <h1>Barbershop Like Bro</h1>
        <section class="left_bar">
            <ul>
                <li><button class="client">Клиенты</button></li>
                <li><button class="hairstyles">Прически</button></li>
                <li><button class="master">Мастера</button></li>
                <li><button class="orders">Заказы</button></li>
            </ul>
        </section>
        <section id="content">
            <h3>Выберите таблицу</h3>
        </section>
        <nav>
            <ul class="buttons">
                <li><button class="add">Добавить</button></li>
                <li><button class="edit">Изменить</button></li>
                <li><button class="delete">Удалить</button></li>
            </ul>
        </nav>
    </div>
<?}?>

</body>
</html>