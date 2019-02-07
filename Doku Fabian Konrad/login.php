<?php
	//Folgender String ist innerhalb des Login-Buttons zu lesen. Er wird verändert, falls der Login erfolglos war.
	$loginstring = "Login";
	$debugstring = "";
	//Datenbank über das Authentifizierungsskript einbinden
	include 'incl/dbconnect.php';
	/*Die PHP-Hauptaktivität dieser Website. Wird mit dem Aufruf der Seite ausgelöst.
	Sucht die Datenbank nach der eingegebenen User-Password-Kombination ab und öffnet direkt die main page
	oder weist auf falschen Input hin. Mit der ersten Zeile wird getestet, ob Inputs vorhanden sind.*/
	session_start();
	if ((isset($_POST["inputEmail"])) && (isset($_POST["inputPassword"])) )
    {
		if ( $authsuccess = tryAuth($_POST["inputEmail"], $_POST["inputPassword"], $db) )
		{
			
			$_SESSION["id"] = $_POST["inputEmail"]; //Session der Session die ID zuweisen.
			header('location:index.php');
		}
		else
		{
			$debugstring = $authsuccess?"Authentifizierung erfolgreich":"User/PW - Kombination gibt es nicht"; //DEBUG gibt aus, ob der auth erfolgreich war oder nicht
			session_destroy();
		}
    }
	else
	{
		session_destroy();
	}	
	function tryAuth(string $inputEmail , string $inputPassword, mysqli $db)
	{	

		//global $debugstring , $loginstring;//DEBUG
		//$debugstring = "Vor dem Include";//DEBUG
		$authsuccess = false;//Authentifizierung war noch nicht erfolgreich
		//$pw_hash = 	sha1 ( $inputPassword); //SHA1-Hash für die Datenbankabfrage generieren //Obsolet bei mysql
		//$debugstring = "Vor der Query. PW_Hash ist: " . $pw_hash ;//DEBUG
		$cred_query = "SELECT COUNT(*)  
			FROM 
				kunde 
			WHERE 
				ID = '".mysqli_real_escape_string( $db , $inputEmail)."'
				AND
				PASSWORD =  SHA2('".mysqli_real_escape_string( $db , $inputPassword)."', 512)		
			"; 
		//$loginstring = $cred_query; //DEBUG; Gibt einfach nur die SQL-Query aus
		//Führt Query aus. Falls die Query den wert false erzeugt, also Fehler auftraten, wird eine Warnung ausgegeben...
		//Das Ergebnis $result ist ein Objekt der Klasse mysqli_result. Diese implementiert das Interface Traversible,
		//ist daher traversierbar ähnlich eines Iterators.
		if( $result = $db->query($cred_query) )
		{
			//$loginstring = "Query eingelesen. Anzahl Zeilen: ". $result -> num_rows; // DEBUG
			//Prüft, ob die Query nicht leer ist
			if ($result->num_rows)
			{
				//global $loginstring, $result, $authsuccess;
				//$loginstring = "Query hat Werte"; // DEBUG
				//Sucht aus dem Ergebnisfeld das erste Subfeld(also die erste Zeile).
				//Offenbar wird die Titelzeile direkt entfernt, also ist $vartrash obsolet
				// dort steht das SQL-Count-Ergebnis. Gibt nun davon den
				//ersten Wert aus. Ist dieser 1, so existiert diese nutzer/PW-Kombination.
				//$vartrash = $result -> fetch_row();//Wirft Titelzeile aus dem Stream
				$amount = sprintf ( "%s" , $result -> fetch_row()[0] ) ; //Erste Zeile, erste Spalte (also der Countwert) wird ausgelesen und in String geparst.
				//$debugstring .= "sprintf-Ergebnis: " . $amount . " Datentyp: " . gettype($amount) ;//DEBUG
				$authsuccess = ($amount=="1"); //Setzt Den Parameter für erfolgreichen login auf true, falls die Count-Zahl 1 ist.
				//$debugstring = "Fehler"; //DEBUG
			}
		}
		return $authsuccess;
	}

?>
<!DOCTYPE html>
<html lang="de">

  <head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Online-Banking Webanwendung">
    <meta name="author" content="Programmiergruppe\Fabian Konrad">

    <title>Online-Banking: Login</title>
	
	<!-- Google Captcha API !-->
	<script src='https://www.google.com/recaptcha/api.js?render=6LcWtYoUAAAAACHMaAGub1Kh7nTslRGZMGreg7X5'></script>
	
    <!-- Bootstrap core CSS-->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="favicon.ico">

<?php //PHP-Wraparound, um den auskommentierten Code auf dem Server zu behalten
/* <!-- Javascript, dass das Server sided PHP ansteuert; AJAX wäre wohl eine besere Alternative !-->
  <script>
	function submitValues()
	{
		//TEST
		Document.getElementByClassName('card-header').item(0).Text = 'TRASSHSSHSH'
	}
  
  </script>
*/ ?>
<script>
		function prepareSubmit()
		{
			grecaptcha.ready(
			function() {
				grecaptcha.execute('6LcWtYoUAAAAACHMaAGub1Kh7nTslRGZMGreg7X5', {action: 'login'})
				.then(function(token) 
					{//Captcha-Request an captcha.php
					    var captchaVerificationConnector = new XMLHttpRequest();
							captchaVerificationConnector.onreadystatechange = function() {
								if (this.readyState == 4 && this.status == 202)//Zurückgesendet und HTTP202 Header
								{
									if(this.responseText == "succ=1")
									{										
										//inputEmail.value() = utoa(inputEmail.value); //Email Base64 codieren, wird erst nötig, wenn der Login über Email läuft.
										//alert("Submit auslösen"); //DEBUG
										if ( ($('#inputEmail').val() != "" ) && ($('#inputPassword').val() != "" ) ) //Absenden, wenn Email und Passwort nicht leer sind.
											{
												$('#SubmitForm').submit();
											}
										//myInput.submit();
									}
								}
								/*
								else
								{
									alert("Captcha resettet");
									//Captcha oder DB-Request war nicht erfolgreich
									grecaptcha.reset();//buganfällig
								}
								*/
							}
						captchaVerificationConnector.open("POST", "captcha.php" , true);
						captchaVerificationConnector.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
						captchaVerificationConnector.send("ukey=" + token.toString());
					});
			});
		}

</script>
</head>
  <body class="bg-dark">
        
    
    <div class="container">
      <div class="card card-login mx-auto mt-5">
        <div class="card-header">Anmelden</div>
        <div class="card-body">
          <form class="myInput" method="post" id="SubmitForm">
            <div class="form-group">
              <div class="form-label-group">
                <input type="text" id="inputEmail" name="inputEmail" class="form-control" placeholder="Email address" required="required" autofocus="autofocus">
                <label for="inputEmail" >Benutzername</label>
              </div>
            </div>
            <div class="form-group">
              <div class="form-label-group">
                <input type="password" id="inputPassword" name="inputPassword" class="form-control" placeholder="Password" required="required">
                <label for="inputPassword" >Passwort</label>
              </div>
            </div>
            <!--<div class="form-group">
              <div class="checkbox">
                <label>
                  <input type="checkbox" id="rememberme" name="rememberme" value="remember-me">
                  Angemeldet bleiben...
                </label>
              </div>
            </div>!-->
			<a type="submit" class="btn btn-primary btn-block" onclick="prepareSubmit()" ><?php echo $loginstring ?></a>
          </form>
		  <div style="size: 100%; text-align:center;"><?php echo $debugstring ?></div>
          <div class="text-center">
            <a class="d-block small mt-3" href="register.php">Register an Account</a>
            <a class="d-block small" href="forgot-password.php">Forgot Password?</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  </body>

</html>

