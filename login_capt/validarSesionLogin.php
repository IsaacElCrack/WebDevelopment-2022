<?php
if(($_SERVER["REQUEST_METHOD"] == "POST")){
	if(!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
		//recaptcha vacio
		header("location:index.php?cp=1");
	} else {
		$secret = '6Lerj-giAAAAAOnzttWpKu27VnlJ-saRnF6CsLjt';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response);
	
	if($response->success) {

		include('conexion/config.php');
		date_default_timezone_set("America/Mexico");
		$sesionDesde   = date("Y-m-d H:i:A");

		//Evitar recibir las variables por metodo $_REQUEST['xxx'];
		//Limpiando las variables para evitar vulnerabilidad o inyeccion SQL en los datos recibidos
		
		$correo = filter_var($_POST['emailUser'], FILTER_SANITIZE_EMAIL);
		if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
		    $emailUser 	= ($_POST['emailUser']);
		}
		
		$passwordUser  = trim($_POST["passwordUser"]); 

		/*https://bugs.mysql.com/bug.php?id=71939
		Nota: hay que ejecutar estas consultas sql
		ALTER TABLE myusers CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
		ALTER DATABASE MyDataBase COLLATE utf8_bin
		*/

		$sqlVerificandoLogin = ("SELECT IdUser, nameUser, emailUser, passwordUser  FROM myusers WHERE emailUser COLLATE utf8_bin='$emailUser'");
		$resultLogin = mysqli_query($con, $sqlVerificandoLogin) or die(mysqli_error($con));;

		if(mysqli_num_rows($resultLogin) == 1){
			while($rowData  = mysqli_fetch_assoc($resultLogin)){
				$passwordBD = $rowData['passwordUser'];
				if(password_verify($passwordUser, $passwordBD)) {
					session_start(); //Creando la sesion ya que los datos son validos
					$_SESSION['IdUser'] 	= $rowData['IdUser']; 
					$_SESSION['nameUser']	= $rowData['nameUser'];
					$_SESSION['emailUser'] 	= $rowData['emailUser'];

					//Actualizando la primera Hora del Login
					$Update = ("UPDATE myusers SET sesionDesde='$sesionDesde' WHERE emailUser='$emailUser' ");
					$resultado = mysqli_query($con, $Update);

					header("location:home.php?a=1");
				}else{
					//echo "Login incorecto";
					header("location:index.php?b=1");
				}
			}

		} 
		else{
			//echo "No existe el correo registrado";
			header("location:index.php?e=1");
		}
	}
 }
}
	
?>
