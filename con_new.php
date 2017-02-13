<?
session_start();
	//подключение к БД

	/*$conn = pg_connect("host=localhost port=5433 dbname=barber user=postgres password=123"); 
if (!$conn) {
		echo "Произошла ошибка соединения с БД.\n";
		exit;
	};*/
	//подключение к БД
	include_once ("config.php");

	define('key',md5('this is a secret key'));

	/*шифрование данных*/
	function encrypt($input){
		return $input;
		//return base64_encode(mcrypt_ecb (MCRYPT_3DES, $key, trim($input), MCRYPT_ENCRYPT));
}

	/*дешифрование данных*/
	function decrypt($input){
		return $input;
		//return trim(mcrypt_ecb (MCRYPT_3DES, $key, base64_decode($input), MCRYPT_DECRYPT));
}

function defender_xss_string($value){
	return filter_var($value, FILTER_SANITIZE_STRING);
}

function defender_xss_int($value){
	return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}

$_GET  = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

	/*вывод данных из таблиц*/
	function sell_db($conn, $table) {
		if($table != 'order_add') {
			$result = pg_query($conn, "SELECT * FROM $table");
			if(!$result) {
				echo "Произошла ошибка запроса.\n";
				exit;
			}
			while($row = pg_fetch_assoc($result)) {
				$mas[] = $row;
			}
		}
		switch ($table) {
			case 'hairstyles':
				foreach ($mas as $key => $value) {
					$table_content .="<tr>
										<td><input type='radio' name='id_hairstyles' value='".$value['id']."'></td>
										<td>".$value['id']."</td>
										<td>".$value['name']."</td>
										<td>".$value['price']."</td>
										<td>".$value['h_time']."</td>
									</tr>";
				}
				return $table_content;
				break;
			case 'master':
				foreach ($mas as $key => $value) {
					$table_content .="<tr>
										<td><input type='radio' name='id_master' value='".$value['id']."'></td>
										<td>".$value['id']."</td>
										<td>".decrypt($value['full_name'])."</td>
										<td>".decrypt($value['qualification'])."</td>
									</tr>";
				}
				return $table_content;
				break;
			case 'client':
				foreach ($mas as $key => $value) {
					$table_content .="<tr>
										<td><input type='radio' name='id_client' value='".$value['id']."'></td>
										<td>".$value['id']."</td>
										<td>".decrypt($value['full_name'])."</td>
										<td>".decrypt($value['type_of_hair'])."</td>
										<td>".$value['age']."</td>
									</tr>";
				}
				return $table_content;
				break;
			case 'orders':
				$result_hairstyle= pg_query($conn, "SELECT id, name FROM hairstyles");
				$result_master = pg_query($conn, "SELECT id, full_name FROM master");
				$result_client = pg_query($conn, "SELECT id, full_name FROM client");

				while($row = pg_fetch_assoc($result_hairstyle)) {
					$mas_hairstyle[] = $row;
				}
				while($row = pg_fetch_assoc($result_master)) {
					$mas_master[] = $row;
				}
				while($row = pg_fetch_assoc($result_client)) {
					$mas_client[] = $row;
				}
				if(!isset($_POST['tax_klient'])) {
					foreach ($mas as $key => $value) {
						foreach ($mas_hairstyle as $key2 => $value2) {
							foreach ($mas_master as $key3 => $value3) {
								foreach ($mas_client as $key4 => $value4) {
									if($value['id_client'] == $value4['id'] AND
										$value['id_master'] == $value3['id'] AND
										$value['id_hairstyles'] == $value2['id']) {
										$table_content .="
										<tr>
											<td><input type='radio' name='id_orders' value='".$value['id']."'></td>
											<td>".$value['id']."</td>
											<td>".$value['id_client']."(".decrypt($value4['full_name']).")</td>
											<td>".$value['id_master']."(".decrypt($value3['full_name']).")</td>
											<td>".$value['id_hairstyles']."(".$value2['name'].")</td>
											<td>".$value['d_data']."</td>
											<td>".$value['o_time']."</td>
										</tr>";
									}
								}
							}
						}
					}
					return $table_content;
				}
				break;
			case "order_add": 
				$result_hairstyles = pg_query($conn, "SELECT id, name FROM hairstyles"); // обращение к бд за именами
				$result_master = pg_query($conn, "SELECT id, full_name FROM master");
				$result_client = pg_query($conn, "SELECT id, full_name FROM client");

				while($row = pg_fetch_assoc($result_hairstyles)) {//создание асоциативаного массива
					$mas_hairstyles[] = $row; // заполняем массив
				}
				while($row = pg_fetch_assoc($result_master)) {
					$mas_master[] = $row;
				}
				while($row = pg_fetch_assoc($result_client)) {
					$mas_client[] = $row;
				}
				if(!isset($_POST['order_client'])) {
					foreach ($mas_hairstyles as $key2 => $value2) {
						$table_content_hairstyles .= "<option value='".$value2['id']."'>".$value2['name']."</option>";
					}
					foreach ($mas_master as $key3 => $value3) {
						$table_content_master .= "<option value='".$value3['id']."'>".decrypt($value3['full_name'])."</option>";
					}
					foreach ($mas_client as $key4 => $value4) {
						$table_content_client .="<option value='".$value4['id']."'>".decrypt($value4['full_name'])."</option>";
					}//создание выборочного списка
					$content = "<div class='add_block'>
									<h2>Заказы</h2>
									<p><label for=''>Шифр клиента : </label>
									<select name='id_client' id=''>".$table_content_client."</select></p>
									<p><label for=''>Шифр мастера : </label>
									<select name='id_master' id=''>".$table_content_master."</select></p>
									<p><label for=''>Шифр прически : </label>
									<select name='id_hairstyles' id=''>".$table_content_hairstyles."</select></p>
									<p><label for=''>Дата : </label>
									<input type='date' name='date'>
									<p><label for=''>Время : </label>
									<input type='time' name='time'>
									<input type='button' class='add_orders' name='add_button' value='Добавить'/>
								</div>";
				}
				return $content;
				break;
			default:
				echo "Выберите таблицу";
				break;
		}
	}
	// вывод данных из таблиц

	//добавление прически
	function add_hairstyles($conn, $name, $price, $h_time) {
		$result = pg_query($conn, "INSERT INTO hairstyles (name, price, h_time) VALUES ('$name', '$price', '$h_time') RETURNING id");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		if($result) echo $mas_result[0]['id'];
		else echo "Произошла ошибка";
	}
	//Добавление клиента
	function add_client($conn, $full_name, $type_of_hair, $age) {
		$age = defender_xss_int($age);
		$full_name = defender_xss_string(encrypt($full_name));
		$type_of_hair = defender_xss_string(encrypt($type_of_hair));
		$result = pg_query($conn, "INSERT INTO client (full_name, type_of_hair, age) VALUES ('$full_name', '$type_of_hair', '$age') RETURNING id");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		if($result) echo $mas_result[0]['id'];
		else echo "Произошла ошибка";
	}
	//добавление мастера
	function add_master($conn, $full_name, $qualification) {
		$full_name = encrypt($full_name);
		$qualification = encrypt($qualification);
		$result = pg_query($conn, "INSERT INTO master (full_name, qualification) VALUES ('$full_name', '$qualification') RETURNING id");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		if($result) echo $mas_result[0]['id'];
		else echo "Произошла ошибка";
	}
	//добавление заказа
	function add_orders($conn, $id_client, $id_master, $id_hairstyles, $date, $time) {
		$result = pg_query($conn, "INSERT INTO orders (id_hairstyles, id_master, id_client, d_data, o_time) VALUES ('$id_hairstyles','$id_master', '$id_client', '$date', '$time') RETURNING id");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		if($result) echo $mas_result[0]['id'];
		else echo "Произошла ошибка";
	}
	//одна запись для редактирования
	function select_to_edit($conn, $id, $n_table) {
		$result = pg_query($conn, "SELECT * FROM $n_table WHERE id=$id");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		return $mas_result;
	}
	//редактирование
	function edit_hairstyles($conn, $name, $price, $h_time, $cheked_id) {
		$result = pg_query($conn, "UPDATE hairstyles SET name='$name', price='$price', h_time='$h_time' WHERE id=$cheked_id");
		if($result) echo "Прическа отредактирована!";
		else echo "Произошла ошибка";
	}
	function edit_client($conn, $full_name, $type_of_hair, $age, $cheked_id) {
		$full_name = defender_xss_string(encrypt($full_name));
		$type_of_hair = defender_xss_string(encrypt($type_of_hair));
		$age = defender_xss_int($age);
		$result = pg_query($conn, "UPDATE client SET full_name='$full_name', type_of_hair='$type_of_hair', age='$age' WHERE id=$cheked_id");
		if($result) echo "Клиент отредактирован!";
		else echo "Произошла ошибка";
	}
	function edit_master($conn, $full_name, $qualification, $cheked_id) {
		$full_name = encrypt($full_name);
		$qualification = encrypt($qualification);
		$result = pg_query($conn, "UPDATE master SET full_name='$full_name', qualification='$qualification' WHERE id=$cheked_id");
		if($result) echo "Мастер отредактирован!";
		else echo "Произошла ошибка";
	}
	function edit_orders($conn, $id_client, $id_master, $id_hairstyles, $cheked_id, $date, $time) {
		$result = pg_query($conn, "UPDATE orders SET id_master='$id_master', id_client='$id_client', id_hairstyles='$id_hairstyles', d_data='$date', o_time='$time' WHERE id=$cheked_id");
		if($result) echo "Заказ отредактированы!";
		else echo "Произошла ошибка";
	}
	//Удаление
	function delete($conn, $id, $n_table) {
		$result = pg_query($conn, "DELETE FROM $n_table WHERE id='$id'");
		if($result) echo "Запись удалена!";
		else echo "Произошла ошибка";
	}
	/*//Запросы
	function req_1($conn, $id) {
		$result = pg_query($conn, "SELECT c.full_name,h.price from client as c inner join orders as o on c.id=o.id_client inner join master as m on m.id=o.id_master inner join hairstyles as h on h.id=o.id_hairstyles where m.id='$id'");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		return $mas_result;
	}
	function req_2($conn, $id) {
		$result = pg_query($conn, "SELECT distinct m.qualification from client as c inner join orders as o on c.id=o.id_client inner join master as m on m.id=o.id_master where c.id='$id';");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		return $mas_result;
	}
	function req_3($conn, $master, $client) {
		$result = pg_query($conn, "SELECT distinct h.name from client as c inner join orders as o on c.id=o.id_client inner join master as m on m.id=o.id_master inner join hairstyles as h on h.id=o.id_hairstyles where c.id='$client' and m.id='$master';");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		return $mas_result;
	}
	function req_4($conn) {
		$result = pg_query($conn, "SELECT distinct h.name from client as c inner join orders as o on c.id=o.id_client inner join hairstyles as h on h.id=o.id_hairstyles where c.type_of_hair='Сухие' or c.age=16;");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		return $mas_result;
	}
	function req_5($conn) {
		$result = pg_query($conn, "select  SUM(h.price) as sum ,m.full_name from orders as o inner join master as m on m.id=o.id_master inner join hairstyles as h on h.id=o.id_hairstyles group by m.full_name having SUM(h.price)= (   	select MAX(sum)  	from ( 		select  SUM(h.price) as sum ,m.full_name 		from orders as o 		inner join master as m on m.id=o.id_master 		inner join hairstyles as h on h.id=o.id_hairstyles 		group by m.full_name 	)as t )");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		return $mas_result;
	}
	function req_6($conn) {
		$result = pg_query($conn, "select m.full_name from  orders as o inner join master as m on m.id=o.id_master inner join hairstyles as h on h.id=o.id_hairstyles group by m.full_name having count(distinct h.name)= ( 	select count(*) 	from hairstyles )");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		return $mas_result;
	}
	function req_7($conn) {
		$result = pg_query($conn, "select c.full_name from  orders as o inner join client as c on c.id=o.id_client inner join master as m on m.id=o.id_master inner join hairstyles as h on h.id=o.id_hairstyles group by c.full_name having count(distinct m.id)= ( 	select count(*) 	from  master )");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		return $mas_result;
	}
	function req_8($conn, $id) {
		$result = pg_query($conn, "select h2.name from hairstyles as h2 where h2.id not in  ( 	select h.id 	from  orders as o 	inner join client as c on c.id=o.id_client 	inner join hairstyles as h on h.id=o.id_hairstyles 	where  c.id='$id'  )");
		while($row = pg_fetch_assoc($result)) {
			$mas_result[] = $row;
		}
		return $mas_result;
	}*/
	
	
	//авторизация
	//авторизация с спомощью usb ключа rutoken https://special.habrahabr.ru/kyocera/p/120990/
	function auth($conn, $login, $pass) {
		if(!empty($login) AND !empty($pass)) {
			//$login = encrypt($login);
			//$pass = md5(trim($pass));
			
			$result = pg_query($conn, "SELECT login, status FROM users WHERE login='$login' AND pass='$pass'");
			if($result) {
				while($row = pg_fetch_assoc($result)) {
					$mas_result[] = $row;
				}
			}
			if($mas_result != ""){
				$_SESSION['name'] = $mas_result[0]['login'];
				$_SESSION['status'] = $mas_result[0]['status'];
				echo "Вы вошли!";
			}
			else{
				echo "Не корректно введен логин/пароль";
/*				$result1 = pg_query($conn, "SELECT login FROM users WHERE login='$login'");
				if($result1) {
					while($row = pg_fetch_assoc($result1)) {
						$mas_result1[] = $row;
					}
				}else{
					echo "Не корректно введен логин/пароль";
				}
				if ($mas_result1 != "") {
					echo "Не корректно введен пароль";
				}
				else {
					$result = pg_query($conn, "INSERT INTO users (login,pass,status) VALUES ('$login','$pass',1)");
					if($result) {
						echo "Вы успешно зарегистрированы";
					}
					else {
						echo "Произошла ошибка";
					}
				}*/
			}
		}
		else {
			echo "Заполните все поля";
		}
	}
	//выход
	function out() {
		unset($_SESSION['key']);
		echo "Вы вышли";
	}

	/*function get_req_3($conn) {
		$client = pg_query($conn, "SELECT id, full_name FROM client");
		$master = pg_query($conn, "SELECT id, full_name FROM master");
		if($client) {
			while($row = pg_fetch_assoc($client)) {
				$mas_client[] = $row;
			}
		}
		if($master) {
			while($row = pg_fetch_assoc($master)) {
				$mas_master[] = $row;
			}
		}
		foreach ($mas_client as $key2 => $value2) {
			$select_content .= "<option value='".$value2['id']."'>".$value2['full_name']."</option>";
		}
		foreach ($mas_master as $key3 => $value3) {
			$select_content2 .= "<option value='".$value3['id']."'>".$value3['full_name']."</option>";
		}
		echo "<div class='req_desc'><p><label for=''>Клиенты </label></p><select name='client' id=''>".$select_content."</select><p><label for=''>Мастер </label></p><select name='master' id=''>".$select_content2."</select> <button class='req_3_obr'>Выполнить</button></div>";
	}*/

	//контроллер
	if(isset($_POST['table']))
	{
		switch ($_POST['table']) {
		//вывод таблиц
		case 'hairstyles':
			$result = sell_db($conn, $_POST['table']);
			echo "<table class='hairstyles'>
				<thead>
					<tr>
						<th></th>
						<th>Шифр</th>
						<th>Название</th>
						<th>Стоимость</th>
						<th>Время</th>
					</tr>
				</thead>
				<tbody>
					".$result."
				</tbody>
			</table>";
			exit;
			break;
		case 'master':
			$result = sell_db($conn, $_POST['table']);
			echo "<table class='master'>
				<thead>
					<tr>
						<th></th>
						<th>Шифр</th>
						<th>ФИО</th>
						<th>Квалификация</th>
					</tr>
				</thead>
				<tbody>
					".$result."
				</tbody>
			</table>";
			exit;
			break;
		case 'client':
			$result = sell_db($conn, $_POST['table']);
			echo "<table class='client'>
				<thead>
					<tr>
						<th></th>
						<th>Шифр</th>
						<th>ФИО</th>
						<th>Тип волос</th>
						<th>Возраст</th>
					</tr>
				</thead>
				<tbody>
					".$result."
				</tbody>
			</table>";
			exit;
			break;
		case 'orders':
			$result = sell_db($conn, $_POST['table']);
			echo "<table class='orders'>
				<thead>
					<tr>
						<th></th>
						<th>Шифр</th>
						<th>Шифр клиента</th>
						<th>Шифр мастера</th>
						<th>Шифр прически</th>
						<th>Дата</th>
						<th>Время</th>
					</tr>
				</thead>
				<tbody>
					".$result."
				</tbody>
			</table>";
			exit;
		case 'order_add':
			$result = sell_db($conn, $_POST['table']);
			echo $result;
			exit;
			break;
		//вывод таблиц
		//добавление в таблицу
		case 'add_hairstyles':
			$name = $_POST['name'];
			$price = $_POST['price'];
			$h_time = $_POST['h_time'];
			add_hairstyles($conn, $name, $price, $h_time);
			exit;
			break;
		case 'add_client':
			$full_name = $_POST['full_name'];
			$type_of_hair = $_POST['type_of_hair'];
			$age = $_POST['age'];
			add_client($conn, $full_name, $type_of_hair, $age);
			exit;
			break;
		case 'add_master':
			$full_name = $_POST['full_name'];
			$qualification = $_POST['qualification'];
			add_master($conn, $full_name, $qualification);
			exit;
			break;
		case 'add_orders':
			$id_hairstyles = $_POST['id_hairstyles'];
			$id_master = $_POST['id_master'];
			$id_client = $_POST['id_client'];
			$date = $_POST['date'];
			$time = $_POST['time'];
			add_orders($conn, $id_client, $id_master, $id_hairstyles, $date, $time);
			exit;
			break;
		//добавление в таблицу
		//Выборка данных для редактирования
		case 'edit':
			$id = $_POST['id'];
			$n_table = $_POST['n_table'];
			$mas = select_to_edit($conn, $id, $n_table);
			if ($n_table == 'hairstyles') {
				echo "<div class='add_block'><h2>Прически</h2><p><label for=''>Шифр : </label><input type='text' disabled name='id' value='".$mas[0]['id']."'/></p><p><label for=''>Название : </label><input type='text' name='name' value='".$mas[0]['name']."'/></p><p><label for=''>Цена: </label><input type='text' name='price' value='".$mas[0]['price']."'/></p><p><label for=''>Время : </label><input type='text' name='h_time' value='".$mas[0]['h_time']."'/></label></p><input type='button' name='edit_button' class='edit_hairstyles' value='Изменить'/></div>";
			}
			else if($n_table == 'master') {
				echo "<div class='add_block'><h2>Мастер</h2><p><label for=''>Шифр : </label><input name='id' disabled value='".$mas[0]['id']."'/></p><p><label for=''>ФИО : </label><input type='text' name='full_name' value='".decrypt($mas[0]['full_name'])."'/></p><p><label for=''>Квалификация : </label><input type='text' name='qualification' value='".decrypt($mas[0]['qualification'])."'/></p><input type='button' name='edit_button' class='edit_master' value='Изменить'/></div>";
			}
			else if($n_table == 'client') {
				echo "<div class='add_block'><h2>Клиент</h2><p><label for=''>Шифр : </label><input name='id' disabled value='".$mas[0]['id']."'/></p><p><label for=''>ФИО : </label><input name='full_name' value='".decrypt($mas[0]['full_name'])."'/></p><p><label for=''>Тип волос : </label><input name='type_of_hair' value='".decrypt($mas[0]['type_of_hair'])."'/></p><p><label for=''>Возраст : </label><input name='age' value='".$mas[0]['age']."'/></p><input type='button' name='edit_button' class='edit_client' value='Изменить'/></div>";
			}
			else if($n_table == 'orders') {
				$result_hairstyles = pg_query($conn, "SELECT id, name FROM hairstyles");
				$result_master = pg_query($conn, "SELECT id, full_name FROM master");
				$result_client = pg_query($conn, "SELECT id, full_name FROM client");

				while($row = pg_fetch_assoc($result_hairstyles)) {
					$mas_hairstyles[] = $row;
				}
				while($row = pg_fetch_assoc($result_master)) {
					$mas_master[] = $row;
				}
				while($row = pg_fetch_assoc($result_client)) {
					$mas_client[] = $row;
				}
				foreach ($mas_hairstyles as $key2 => $value2) {
					if ($mas[0]['id_hairstyles'] == $value2['id']) $table_content_hairstyles .= "<option selected class='selected' value='".$value2['id']."'>".$value2['name']."</option>";
					$table_content_hairstyles .= "<option value='".$value2['id']."'>".$value2['name']."</option>";
				}
				foreach ($mas_master as $key3 => $value3) {
					if ($mas[0]['id_master'] == $value3['id']) $table_content_master .= "<option selected class='selected' value='".$value3['id']."'>".decrypt($value3['full_name'])."</option>";
					$table_content_master .= "<option value='".$value3['id']."'>".decrypt($value3['full_name'])."</option>";
				}
				foreach ($mas_client as $key4 => $value4) {
					if ($mas[0]['id_client'] == $value4['id']) $table_content_client .="<option selected class='selected' value='".$value4['id']."'>".decrypt($value4['full_name'])."</option>";
					$table_content_client .="<option value='".$value4['id']."'>".decrypt($value4['full_name'])."</option>";
				}
				echo "<div class='add_block'>
					<h2>Заказы</h2>
					<p><label for=''>Шифр : </label><input name='id' disabled value='".$mas[0]['id']."'/></p>
					<p><label for=''>Клиент: </label>
					<select name='id_client' id='' >".$table_content_client."</select></p>
					<p><label for=''>Мастер: </label>
					<select name='id_master' id='' >".$table_content_master."</select></p>
					<p><label for=''>Прическа: </label>
					<select name='id_hairstyles' id='' >".$table_content_hairstyles."</select></p>
					<p><label for=''>Дата : </label>
					<input type='date' name='date' value='".$mas[0]['d_date']."'>
					<p><label for=''>Время : </label>
					<input type='time' name='time' value='".$mas[0]['o_time']."'>
					<input type='button' class='edit_orders' name='add_button' value='Изменить'/>
				</div>";
			}
			exit;
			break;
		//Выборка данных для редактирования
		//Редактирование
		case 'edit_hairstyles':
			$cheked_id = trim($_POST['cheked_id']);
			$name = trim($_POST['name']);
			$price = trim($_POST['price']);
			$h_time = trim($_POST['h_time']);
			edit_hairstyles($conn, $name, $price, $h_time, $cheked_id);
			exit;
			break;
		case 'edit_client':
			$cheked_id = trim($_POST['cheked_id']);
			$full_name = trim($_POST['full_name']);
			$type_of_hair = trim($_POST['type_of_hair']);
			$age = trim($_POST['age']);
			//echo $cheked_id.", ".$name.", ".$type_of_hair.", ".$age;
			edit_client($conn, $full_name, $type_of_hair, $age, $cheked_id);
			exit;
			break;
		case 'edit_master':
			$cheked_id = trim($_POST['cheked_id']);
			$full_name = trim($_POST['full_name']);
			$qualification = trim($_POST['qualification']);
			edit_master($conn, $full_name, $qualification, $cheked_id);
			exit;
			break;
		case 'edit_orders':
			$cheked_id = trim($_POST['cheked_id']);
			$id_master = trim($_POST['id_master']);
			$id_hairstyles = trim($_POST['id_hairstyles']);
			$id_client = trim($_POST['id_client']);
			$date = trim($_POST['date']);
			$time = trim($_POST['time']);
			edit_orders($conn, $id_client, $id_master, $id_hairstyles, $cheked_id, $date, $time);
			exit;
			break;
		//Удаление
		case 'del':
			$id = $_POST['id'];
			$n_table = $_POST['n_table'];
			delete($conn, $id, $n_table);
			exit;
			break;
		//Запросы
		case 'req_1':
			$id = $_POST['id'];
			$mas = req_1($conn, $id);
			foreach ($mas as $key => $value) {
				$val .= "<li>".$value['full_name']." (".$value['price'].")</li>";
			}
			echo "<ul class='result_req1'>".$val."</ul>";
			exit;
			break;
		case 'req_2':
			$id = $_POST['id'];
			$mas = req_2($conn ,$id);
			if(empty($mas)) echo "Нет такого";
			else {
				foreach ($mas as $key => $value) {
					$val .= "<li>".$value['qualification']."</li>";
				}
				echo "<ul class='result_req1'>".$val."</ul>";
			}
			exit;
		case 'req_3':
			$master = $_POST['master'];
			$client = $_POST['client'];
			$mas = req_3($conn, $master, $client);
			if(empty($mas)) echo "<p class='not_data'>Нет такого<p>";
			else {
				foreach ($mas as $key => $value) {
					$val .= "<li>".$value['name']."</li>";
				}
				echo "<ul class='result_req1'>".$val."</ul>";
			}
			exit;
		case 'req_4':
			$mas = req_4($conn);
			foreach ($mas as $key => $value) {
				$val .= "<li>".$value['name']."</li>";
			}
			echo "<ul class='result_req1'>".$val."</ul>";
			exit;
			break;
		case 'req_5':
			$mas = req_5($conn);
			foreach ($mas as $key => $value) {
				$val .= "<tr><td>".$value['full_name']."</td><td>".$value['sum']."</td></tr>";
			}
			echo "<table class='result_req1'>".$val."</table>";
			exit;
			break;
		case 'req_6':
			$mas = req_6($conn);
			if(empty($mas)) echo "Нет такого";
			else {
				foreach ($mas as $key => $value) {
					$val .= "<tr><td>".$value['full_name']."</td></tr>";
				}
				echo "<table class='result_req1'>".$val."</table>";
			}
			exit;
			break;
		case 'req_7':
			$mas = req_7($conn);
			if(empty($mas)) echo "Нет такого";
			else {
				foreach ($mas as $key => $value) {
					$val .= "<li>".$value['full_name']."</li>";
				}
				echo "<ul class='result_req1'>".$val."</ul>";
			}
			exit;
			break;
		case 'req_8':
			$id = $_POST['id'];
			$mas = req_8($conn, $id);
			foreach ($mas as $key => $value) {
				$val .= "<li>".$value['name']."</li>";
			}
			echo "<ul class='result_req1'>".$val."</ul>";
			exit;
			break;
		case 'req_3_desc':
			get_req_3($conn);
			exit;
			break;
		case 'other_z':
			other_z($conn);
			exit;
			break;
		case 'auth':
			auth($conn, $_POST['login'], $_POST['pass']);
			exit;
			break;
		case 'out':
			out();
			exit;
			break;
		default:
			echo "Выберите таблицу";
			break;
		}
	}
	//контроллер

?>