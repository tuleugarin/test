<?php

function get_user_by_login($login){
	$pdo = new PDO("mysql:host=localhost;dbname=first_project;","root", "");

	$sql = "SELECT * FROM list_users WHERE login=:login";

	$statement = $pdo->prepare($sql);
	$statement->execute(['login'=>$login]);
	$user = $statement-> fetch(PDO::FETCH_ASSOC);
	return $user;
}
function set_flash_message($name, $message){

	$_SESSION[$name] = $message;
}
function redirect_to($path){
	header("Location: {$path}");
	exit;
}
function add_user($login, $password){
	$pdo = new PDO("mysql:host=localhost;dbname=first_project;","root", "");

	$sql = "INSERT INTO list_users (login, password) VALUES (:login, :password)";

	$statement = $pdo->prepare($sql);
	$result = $statement->execute([
		'login'=>$login,
		'password'=>password_hash($password, PASSWORD_DEFAULT)
	]);

	return $pdo->lastInsertId();
}
function display_flash_message($name){
	if (isset($_SESSION[$name])){
		echo "<div class=\"alert alert-{$name} text-dark\" role=\"alert\">{$_SESSION[$name]}</div>";
               unset($_SESSION[$name]);
    }
}
/*Эта функция чистит сессии при нажатии на кнопку ВЫХОД*/
function Logout(){

	// Делаем cookies устаревшими (единственный способ их удалит)
	setcookie('user_id', '', time() -1 );// -1 сек пожже

	// Сброс сессии
	unset($_SESSION['is_logged_in_id']);
	unset($_SESSION['login']);
}
/*создал таблицу list_status там 2 строки id и id_user-его
я связал с id строкой таблицы зарегистрированных пользователей
чтобы  выбрат среди них админов */
function get_admin(){
	$pdo = new PDO("mysql:host=localhost;dbname=first_project;", "root", "");

	$sql = "SELECT * FROM list_status WHERE id_user";

	$statement = $pdo->prepare($sql);
	$statement->execute(['id_user'=>$users]);
	$users = $statement->fetchAll(PDO::FETCH_ASSOC);

	foreach ($users as $key => $value)
		if ($value['id_user']==$_SESSION["is_logged_in_id"])
			return true;//тут я сравнил айди номер из сессии с айди номерами из таблицы с админами если 1 тогда он админ
}
/* Авторизация */
function authorization_check(){
	// Если в контексте сессии не установлен, то мы будем брать их из куки
	if ( !isset($_SESSION['is_logged_in_id']) && isset($_COOKIE['user_id'])){
		$_SESSION["is_logged_in_id"] = $_COOKIE["user_id"];
	}
	// Если идентификатор пуст, то он неавторизованный
	if (empty($_SESSION["is_logged_in_id"])){
		header("Location: /page_login.php");
				exit;
	}
}
/*Эта функция определяет если переменная не пустая
тогда он АДМИН и ему надо показать кнопку иначе не показыват */
function display_button_create($name)	{
		if (!empty($name)) {
			echo "<a class=\"btn btn-success\" href=\"create_user.php\">Добавить</a>";
		}
}
/* функция опции настройки только для юзера*/
function display_settings(){
			echo  " <i class=\"fal fas fa-cog fa-fw d-inline-block ml-1 fs-md\"></i>
                   <i class=\"fal fa-angle-down d-inline-block ml-1 fs-md\"></i></a>
		   <div class=\"dropdown-menu\">
			        <a class=\"dropdown-item\" href=\"edit.html\">
			            <i class=\"fa fa-edit\"></i>
			        Редактировать</a>
			        <a class=\"dropdown-item\" href=\"security.html\">
			            <i class=\"fa fa-lock\"></i>
			        Безопасность</a>
			        <a class=\"dropdown-item\" href=\"status.html\">
			            <i class=\"fa fa-sun\"></i>
			        Установить статус</a>
			        <a class=\"dropdown-item\" href=\"media.html\">
			            <i class=\"fa fa-camera\"></i>
			            Загрузить аватар
			        </a>
			        <a href=\"#\" class=\"dropdown-item\" onclick=\"return confirm('are you sure?')\">
			            <i class=\"fa fa-window-close\"></i>
			            Удалить
			        </a>
			    </div>";
}
/* Функция вывода данных из списка пользователей */
function info_card(){
		$pdo = new PDO("mysql:host=localhost;dbname=first_project;", "root", "");

		$sql = "SELECT * FROM list_card";

		$statment = $pdo -> prepare($sql);
        $statment -> execute();
        $card = $statment ->fetchAll(PDO::FETCH_ASSOC);
        return $card;
}
/* Функция для создания дополнительных данных юзера */
function set_info_card($id_new_user, $upload_img, $username, $title, $tel, $address){
	$pdo = new PDO("mysql:host=localhost;dbname=first_project;", "root", "");
	$sql = "INSERT INTO list_card (id, img, username, title, tel, address) VALUES (:id, :img, :username, :title, :tel, :address)";
	$statement = $pdo->prepare($sql);
	$result = $statement->execute([
		'id'=>$id_new_user,
		'img'=>$upload_img,
		'username'=>$username,
		'title'=>$title,
		'tel'=>$tel,
		'address'=>$address,
	]);

	return $pdo->lastInsertId();
}
/* Функция загрузки аватара на сервер */
function upload_file($file){
	// Если не прекрепили файл
	if (empty($file['name'])){
		set_flash_message("danger", "Файл не выбран");
		redirect_to("create_user.php");
		return;
	}
	// Если размер файла большой
	if (($file['size'] > 1000000)){
		set_flash_message("danger", "Файл не должен быть больше 1Мб");
		redirect_to("create_user.php");
		return;
	}
	// Типы допустимых файлов
	$types = ['image/png', 'image/gif', 'image/jpg', 'image/jpeg'];
	if (!in_array($file['type'], $types)){
		set_flash_message("danger", "Тип загружаемого файла не изображение");
		redirect_to("create_user.php");
		return;
	}
	// Копируем файл в директорию
	$file_name = 'img/demo/avatars/'."avatar-".uniqid().".png";
	if (move_uploaded_file($file["tmp_name"], $file_name)){
		set_flash_message("success", "Файл загружен");

	}
	else{
		set_flash_message("danger", "Файл не загружен");
		redirect_to("create_user.php");
	}
}

?>
