<?php
	include __DIR__.'//mailagent.php';
	include 'incl/dbconnect.php'; //Datenbank-Skript einbinden
	define ("VALIDITY_TIMESPAN", 2*240000); //Gültigkeit auf 48 Stunden fixieren
	$userEmail = ""; //User-Email-Adresse als Variable einspeichern.
	if ((isset($_POST["inputEmail"]))) //Wenn dieser Array-Key existiert, also wenn eine Email-Adresse eingegeben wurde, folgendes ausführen:
	{
		$userEmail = $_POST["inputEmail"];
		if($kundenID = queryEmail( $userEmail , $db ) )
		{
			//$timestamp = time(); //DEBUG
			$privatekey = bin2hex(openssl_random_pseudo_bytes ( 512 )); //1024-Zeichen langen Key erstellen
			//$publickey = openssl_digest ( $privatekey, "sha512" );//DEBUG //128 Zeichen langer Public Key für die Datenbank generieren
			//echo "privatekey: " . $privatekey; //DEBUG 
			//echo "\npublickey: " . $publickey; //DEBUG
			storeRecovery($privatekey, VALIDITY_TIMESPAN, $kundenID, $db);
			$recoveryLink = "https://194.95.221.67/recover-password.php?key=" . $privatekey;
			//echo $recoveryLink ; //DEBUG
			recoveryMail($userEmail, $recoveryLink );
		}
	}
	
	function queryEmail( string $inputEmail , mysqli $db)
	{
		$kundenID = "";
		$email_query = "SELECT kunde.ID  
			FROM 
				kunde 
			WHERE 
				EMAIL = '".mysqli_real_escape_string( $db , $inputEmail)."';
			"; 
		/**********************************************************************
		Alternative Query, die Sicherstellt, dass der Key nicht nochmals generiert wird, die aber bei einer geänderten Email eine erneute Anfrage zulässt
			"SELECT `kunde`.ID FROM `kunde`
			JOIN `recovery`
			ON `kunde`.`EMAIL` = '".mysqli_real_escape_string( $db , $inputEmail)."'
			WHERE `recovery`.KUNDE_ID <> `kunde`.ID; "
		**********************************************************************/
		
		//Führt Query aus. Falls die Query den wert false erzeugt, also Fehler auftraten, wird eine Warnung ausgegeben...
		//Das Ergebnis $result ist ein Objekt der Klasse mysqli_result. Diese implementiert das Interface Traversible,
		//ist daher traversierbar ähnlich eines Iterators.
		if( $result = $db->query($email_query) )
		{
			//Prüft, ob die Query nicht leer ist
			if ($result->num_rows)
			{
				$kundenID = sprintf ( "%s" , $result -> fetch_row()[0] );//Erste spalte der Tabelle in String auslesen
				//$kundenID = ($amount=="1"); //Ausgeben, ob der Count der SQL-Query den Wert 1 hat.
			}
		}
		return $kundenID;
	}
	
	//Recovery in der Datenbank eintragen. Bitte für Zeit die Anzahl der Sekunden geben
	function storeRecovery (string $privatekey, int $validityTimespan , string $kundenID, mysqli $db)
	{
		flushRecovery ( $kundenID, $db); //Alten Recovery-Key löschen.
		$update_query = "INSERT INTO recovery 
				( RECOVERY_KEY, EXPIRY, KUNDE_ID ) 
			VALUES
				(
				 SHA2('".mysqli_real_escape_string( $db , $privatekey)."', 512) ,
				 ADDTIME ( CURRENT_TIMESTAMP() , ".mysqli_real_escape_string( $db , $validityTimespan)." ),
				 '".mysqli_real_escape_string( $db , $kundenID)."' 
				 );";
		return 	$db -> query($update_query); //führe Query aus und gib den Erfolg als bool zurück
	}
	
	function flushRecovery(string $kundenID , mysqli $db) //Achtung, diese Funktion kann mit "*" die gesamte Tabelle löschen
	{
		$flushQuery = "DELETE FROM recovery
		WHERE KUNDE_ID = '".mysqli_real_escape_string( $db , $kundenID)."';";
		return $db -> query($flushQuery);//Gibt zurück, ob die Löschung erfolgreich war oder einen Fehler ausgab.
	}
?>
<!DOCTYPE html>
<html lang="de">

  <head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Passwort zurücksetzen">
    <meta name="author" content="Programmierprojekt FHWS/Fabian Konrad">

    <title>Onlinebanking - Passwort vergessen</title>

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
	<script>
			function prepareSubmit()
			{
				grecaptcha.ready(
				function() {
					grecaptcha.execute('6LcWtYoUAAAAACHMaAGub1Kh7nTslRGZMGreg7X5', {action: 'forgot_password'})
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
											if($('#inputEmail').val() != "" )
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
        <div class="card-header">Passwort zurücksetzen</div>
        <div class="card-body">
          <div class="text-center mb-4">
            <h4>Haben Sie Ihr Passwort vergessen?</h4>
            <p>Geben Sie hierzu Ihre Email-Adresse ein. Sie werden eine Antwort mit Instruktionen zum Zurücksetzen des Passworts erhalten.</p>
          </div>
          <form class="myInput" method="post" id="SubmitForm">
            <div class="form-group">
              <div class="form-label-group">
                <input type="email" id="inputEmail" name="inputEmail" class="form-control" placeholder="Email-Adresse" required="required" autofocus="autofocus">
                <label for="inputEmail">Enter email address</label>
              </div>
            </div>
            <a type="submit" class="btn btn-primary btn-block" onclick="prepareSubmit()" >Passwort zurücksetzen</a>
          </form>
          <div class="text-center">
            <a class="d-block small mt-3" href="register.php">Neuen Account registrieren</a>
            <a class="d-block small" href="login.php">Zum Login</a>
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
