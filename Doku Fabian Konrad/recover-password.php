<?php
	//Aus einer EMail abgerufenen Link anwenden, um Passwort neu zu setzen.

	//Dieser String beschreibt das, was im Textfeld des Buttons angezeigt wird.
	//$registerstring = "Passwort zurücksetzen"; //DEBUG, obsolet
	
	
	
	//Variablen die das HTML Steuern
	$modal_title = ""; //Der modal_title wird erst gesetzt, sobald das Modal aufgerufen wird.
	$dialogHTML = "";	//Der HTML-Inhalt des Modals
	$showModal = true; //Modal grundsätzlich  anzeigen, dies ist das Attribut , das dies entscheidet.
	//$modal_footer = true; //Bei false wird im Modal ein Button zum Schließen geöffnet
	//Bei True wird ein Button 'Weiter' geöffnet
	
	
	//Variablen, die Daten beinhalten
	$kundenID = ""; //Die KundenID für die das Passwort zurückgesetzt werden soll. Diese ist entweder leer oder wird durch das Get definiert.
	$userKey = "";  //Dieser Key wird über den aus der Mail erhaltenen Link übertragen
	
	//Datenbank über das Authentifizierungsskript einbinden
	include 'incl/dbconnect.php';
	//GET-Modus des Dokuments, überprüft, ob der Key in der URL übertragen wurde.
	if ((isset($_GET["key"]))) //Überprüfung: ist in dem Link aus der Email ein Key, um das Passwort zurückzusetzen.
    {
		$userKey = $_GET["key"];
		$kundenID = checkToken($userKey, $db);
		if( $kundenID != "" ) //Ist der Key valide, wird folgendes ausgeführt:
		{
			$showModal = false; //Das Modal nicht anzeigen, da der Key ein Neusetzen das Passworts zulässt.
		}
		else
		{
		//$showModal = true; //obsolet, Modal ist mit diesem Wert initialisiert.
		header("HTTP/1.0 403 Forbidden");
		$modal_title = "Ungültiger Recoverylink";
		$dialogHTML = "<p>Dieser Link ist abgelaufen oder ungültig.</p>";
		//$modal_footer = true;
		}
		
    }
	//POST-Modus des Dokuments. Dieser erhält die Inhalte des Formulars und verarbeitet diese. Er kann nur 
	//verwendet werden, wenn der Key im GET-Modus erfolgreich überprüft wurde, weil nur so Formulardaten eingegeben werden
	else if ((isset($_POST["userKey"]) ) && (isset($_POST["kundenID"])) && (isset($_POST["inputPassword"])) )
	{
		$kundenID = $_POST["kundenID"];
		if(checkToken($_POST["userKey"], $db) ) //Wenn das Token auch im POST korrekt war...
		{
			flushRecovery( $kundenID , $db );
			changePassword( $kundenID , $_POST["inputPassword"] , $db);
			$modal_title = "Passwort erfolgreich geändert";
		}
		//Wenn das Token falsch war gar nichts tun, da es nur bei einer manuellen Eingabe falsch sein kann.
	}
	else
	{
		header("HTTP/1.0 403 Forbidden");
		$modal_title = "Das hat nicht funktioniert";
		$dialogHTML = "<p>Dieser Link ist abgelaufen oder ungültig.</p>";
	}
	
	function changePassword(string $id , string $inputPassword , mysqli $db )
	{
		$update_query = " UPDATE kunde
							SET PASSWORD = SHA2('".mysqli_real_escape_string( $db , $inputPassword)."', 512)
						  WHERE 
							ID = '".mysqli_real_escape_string( $db , $id)."'";
		return $db -> query ($update_query);					
	}
	function checkToken(string $key, mysqli $db) //Diese Funktion geht davon aus, dass es sich um Unique Keys handelt.
	{
		$kundenID = "";
		//echo "jetzt Token checken\nKey hat den Wert:\n" . $key; //DEBUG
		//echo "\n\nder daraus generierte Public key ist:\n" . openssl_digest ( $key, "sha512" );
		$query = "SELECT KUNDE_ID FROM recovery
				WHERE
					RECOVERY_KEY = SHA2('".mysqli_real_escape_string( $db , $key)."' , 512)
				AND
					EXPIRY >= CURRENT_TIMESTAMP()
					;";
		if( $result = $db -> query($query) ) //Versuche die Datenbank zu öffnen
		{//Prüft, ob die Query nicht leer ist
			if ($result->num_rows)
			{
				//echo "Query ist nicht leer"; //DEBUG
				$kundenID = sprintf ( "%s" , $result -> fetch_row()[0] );//Erste spalte der Tabelle in String auslesen
			}
		}
		//echo $kundenID; //DEBUG
		return $kundenID;
	}
	
	function flushRecovery(string $kundenID , mysqli $db) //Aus Forgot-Password: Hier werden die Recovery-Token gelöscht.
	{
		$flushQuery = "DELETE FROM recovery
		WHERE KUNDE_ID = '".mysqli_real_escape_string( $db , $kundenID)."';";
		return $db -> query($flushQuery);//Gibt zurück, ob die Löschung erfolgreich war oder einen Fehler ausgab.
	}
	//War von Register.php
	function checkEmail( string $b64email, mysqli $db)
	{
		$query =
		"SELECT COUNT(*)
		FROM
			kunde
		WHERE
			EMAIL =  FROM_BASE64('".mysqli_real_escape_string( $db , $b64email)."')
		";
		//Das Ergebnis $result ist ein Objekt der Klasse mysqli_result. Diese implementiert das Interface Traversible,
		//ist daher traversierbar ähnlich eines Iterators.
		if($result = $db->query($query) )
		{
			//Prüft, ob die Query nicht leer ist
			if ($result->num_rows)
			{
				//Sucht aus dem Ergebnisfeld das erste Subfeld(also die erste Zeile).
				//Dort steht das SQL-Count-Ergebnis. Gibt nun davon den
				//ersten Wert aus. Ist dieser 1, so existiert diese Email.
				$amount = sprintf ( "%s" , $result -> fetch_row()[0] ) ; //Erste Zeile,
				return !($amount=="0");//Gibt true zurück, wenn die Mail nicht genau 0 mal existiert.
			}
		}
	}
	//War von Register.php
	function insertDb( string $b64email, string $b64nachname, string $b64vorname, string $password, mysqli $db)
	{
		//Fügt die aus der Anfrage in die Datenbank ein
		$query =
		"INSERT INTO
			kunde (NAME, VORNAME, PASSWORD, EMAIL)
		VALUES
			(
			FROM_BASE64('".mysqli_real_escape_string( $db , $b64nachname)."'),
			FROM_BASE64('".mysqli_real_escape_string( $db , $b64vorname)."'),
			SHA2('".mysqli_real_escape_string( $db , $password)."', 512),
			FROM_BASE64('".mysqli_real_escape_string( $db , $b64email)."')
			)";
		return ($db->query($query));//gibt true aus wenn die query erfolgreich war, false, wenn nicht.
	}
				
	//War von Register.php
	function checkInsertion ( string $b64email, string $b64nachname, string $b64vorname, string $password, mysqli $db)
	{
		//Sucht zu Beginn innerhalb der SQL-Tabelle nach den eben generierten Eingaben
		$query =
		"SELECT
			ID, VORNAME, NAME , EMAIL 
		FROM
			kunde
		WHERE
			NAME = FROM_BASE64('".mysqli_real_escape_string( $db , $b64nachname)."')
		AND
			VORNAME = FROM_BASE64('".mysqli_real_escape_string( $db , $b64vorname)."')
		AND
			PASSWORD = SHA2('".mysqli_real_escape_string( $db , $password)."', 512)
		AND
			EMAIL = FROM_BASE64('".mysqli_real_escape_string( $db , $b64email)."')
			";
		//echo $query; //DEBUG
		//global $registerstring; //DEBUG
		//$registerstring = $query; //DEBUG
		if ( $result = $db->query($query) )
		{
			//error(); //DEBUG
			$row = $result -> fetch_row();
			$res_html = 
			sprintf(
			"<table class=\"table\">
			<thead>
				<tr>
				Folgende Daten wurden in unserem System registriert:
				</tr>
			</thead>
			<tbody>
				<tr>
				<th scope=\"row\">Kundennummer</th>
				<td>%d</td>
				</tr>
				<tr>
				<th scope=\"row\">Vorname</th>
				<td>%s</td>
				</tr>
				<tr>
				<th scope=\"row\">Nachname</th>
				<td>%s</td>
				</tr>
				<tr>
				<th scope=\"row\">Email</th>
				<td>%s</td>
				</tr>
			</tbody>
			</table>",
			$row[0],
			$row[1],
			$row[2],
			$row[3]);
			//global $showModal, $dialogHTML; //DEBUG
			//$dialogHTML = $res_html; //DEBUG
			//$showModal=true; //DEBUG
			//error(); //DEBUG
			return $res_html;			
		}
		else
		{
			//global $showModal, $dialogHTML; //DEBUG
			//$dialogHTML = "Query gescheitert"; //DEBUG
			//$showModal=true; //DEBUG
			//error();//DEBUG
			return "";//Leeren String ausgeben, wenn die Query scheitert.
		}
	}
	

?>

<!DOCTYPE html>
<html lang="de">

  <head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Passwort-Recovery-Fenster">
    <meta name="author" content="Fabian Konrad">

    <title>Onlinebanking - Neues Passwort eingeben</title>
	
    <!-- Bootstrap core CSS-->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="favicon.ico">

	<!-- Google Captcha API !-->
	<script src='https://www.google.com/recaptcha/api.js?render=6LcWtYoUAAAAACHMaAGub1Kh7nTslRGZMGreg7X5'></script>
	
  <script>
		/*Das Skript stellt fest, ob überall ein Input vorhanden ist und ob die Passwörter gleich sind.*/
		var allFormControls; //Alle Input-Textfelder
		function findPWtags (allFormControls) //Die beiden Password-Tags suchen
		{
			var alloftype = new Array(2);
			let n=0;
			for(let k = 0; k < allFormControls.length; k++) {
								if(allFormControls[k].type.toLowerCase() == "password")
				{
					alloftype[n++] = allFormControls[k];
				}
			}
			return alloftype;
		}
		
		function modalInputChanger ()//Funktion, die das Modal ausblendet und das Inputfeld wieder einblendet.
		{
			$('#dialogModal').hide();
			document.getElementById("inputContainer").style.display = "block";
		}
		
		var allPWtags, button;
		document.addEventListener('DOMContentLoaded',function()
		{	
			allFormControls = document.getElementsByClassName("form-control");
			allPWtags = findPWtags(allFormControls); //Beide Password-Tags als var fixieren
			button = document.getElementsByClassName("btn-block")[0]; //Button initialisieren
			button.disabled = true;
			for ( var k = 0; k<allFormControls.length; k++ )
				allFormControls[k].oninput = () => {checkInputs();} //führt checkInputs als Callback-Funktion aus
			<?php //PHP öffnet abhängig vom Input das Modal oder den inputContainer
			if($showModal)
			{
				//echo "$('#inputContainer').hide();";
				echo "\ndocument.getElementById(\"inputContainer\").style.display = \"none\";";
				echo "$('#dialogModal').show()";
			}
			else
			{
				echo "document.getElementById(\"inputContainer\").style.display = \"block\";\n\n";
				//echo "$('#inputContainer').show();\n";
			}
			?>
		},false);

		function inputsHaveContent(f)
		{
			for (let k = 0; k < f.length; k++)
			{
				if (f[k].value == "") return false;//Wenn ein einziges Input-Fenster keinen Content hat, wird sofort false ausgegeben
			}
			return true;//true wird ausgegeben, wenn kein Tag keinen Inhalt hatte
		}
		
		function checkInputs() {//Funktion, die ausgeführt wird, wenn die Inputwerte verändert werden.
			var button;
			if (inputsHaveContent(allFormControls) && ( allPWtags[0].value == allPWtags[1].value))
			{

				this.button.disabled = false;
			}
			else this.button.disabled = true;
		}
		
		//Dies ist die Input-Handling-Sektion des Skripts
		
		// ucs-2 string to base64 encoded ascii noted by Johan Sundström
		function utoa(str) {
			return window.btoa(unescape(encodeURIComponent(str)));
		}
		// base64 encoded ascii to ucs-2 string noted by Johan Sundström
		function atou(str) {
			return decodeURIComponent(escape(window.atob(str)));
		}

		//Diese Funktion bereitet den Input auf, führt ein Captcha durch und löst anschließend das Submit der myInput - Form aus
		function prepareSubmit()
		{
			grecaptcha.ready(
			function() {
				grecaptcha.execute('6LcWtYoUAAAAACHMaAGub1Kh7nTslRGZMGreg7X5', {action: 'recover_password'})
				.then(function(token) 
					{//Captcha-Request an captcha.php
					    var captchaVerificationConnector = new XMLHttpRequest();
							captchaVerificationConnector.onreadystatechange = function() {
								if (this.readyState == 4 && this.status == 202)//Zurückgesendet und HTTP202 Header
								{
									if(this.responseText == "succ=1")
									{										
										myInput.submit();
									}
								}
								else
								{
									//Captcha oder DB-Push war nicht erfolgreich
								
								}
							}
						captchaVerificationConnector.open("POST", "captcha.php" , true);
						captchaVerificationConnector.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
						captchaVerificationConnector.send("ukey=" + token.toString());
					});
			});
		}
		
		function nextPage()
		{
			window.location="/index.php";
		}
		

		
  </script>
  </head>
  <body class="bg-dark">

    <div class="container" id="inputContainer" style="display: none;">
      <div class="card card-login mx-auto mt-5">
        <div class="card-header">Neues Passwort erstellen</div>
        <div class="card-body">
          <form name="myInput" id="myInput" method="post" action="/recover-password.php" >
            <div class="form-group">
              
                  <div class="form-label-group">
                    <input type="password" id="inputPassword" name="inputPassword" class="form-control" placeholder="Passwort" required="required">
                    <label for="inputPassword">Passwort</label>
                  </div>
                
                
                  <div class="form-label-group mt-3">
                    <input type="password" id="confirmPassword" class="form-control" placeholder="Passwort bestätigen" required="required">
                    <label for="confirmPassword">Passwort bestätigen</label>
                  </div>
				  <?php/* KundenID und Key zur Überprüfung der Daten versteckt in das Formular hinzufügen */?>
				  <input type="hidden" id="kundenID" name="kundenID" value=<?php echo $kundenID ?> />
				  <input type="hidden" id="userKey" name="userKey" value=<?php echo $userKey ?> />
                
             
            </div>
            <input class="btn btn-primary btn-block" disabled = "true" value="Passwort zurücksetzen" onclick="prepareSubmit()"/>
          </form>
          <div class="text-center">
            <a class="d-block small mt-3" href="login.php">Zum Login</a>
            <a class="d-block small" href="register.php">Registrieren...</a>
          </div>
        </div>
      </div>
    </div>
	<!-- Modal einbinden -->
	<div class="modal" id="dialogModal" tabindex="-1" role="dialog" aria-labelledby="dialogModalCenterTitle">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="dialogModalLongTitle"><?php echo $modal_title ?></h5>
			</button>
		</div>
		<div class="modal-body">
			<?php echo $dialogHTML ?>
		</div>
		<div class="modal-footer">
			<?php/*Kommt von Register.php. hier werden jedoch keine zwei unterschiedliche Buttons gebraucht.<button type="button" class="btn btn-secondary" onclick="modalInputChanger()" 	style="display: echo $modal_footer"none":"block" "Schließen/button */?>
			<button type="button" class="btn btn-primary" 	onclick="nextPage()" 			style="display: block" >Zur Startseite</button>
			<!--?php echo $modal_footer ?!-->
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

