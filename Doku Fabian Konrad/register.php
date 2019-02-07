<?php
	//MailAgent mit dessen Includes zuerst einbinden.
	include __DIR__.'//mailAgent.php' ;
	//Datenbank über das Authentifizierungsskript einbinden
	include 'incl/dbconnect.php';
	define ("VALIDITY_TIMESPAN", "INTERVAL 2 DAY" ); //Gültigkeit auf 48 Stunden fixieren
	//Dieser String beschreibt das, was im Textfeld des Buttons angezeigt wird.
	$registerstring = "Registrieren";
	$modal_title = ""; //Überschrift des Modals
	$dialogHTML = ""; //Inneres HTML im Modal
	$showModal = false;
	$modal_footer = false; //Bei false wird im Modal ein Button zum Schließen geöffnet
	$activationURL = "https://194.95.221.67/activate-account.php?key="; //TODOTODOTODO Das muss noch definiert werden.
	
	//Bei True wird ein Button 'Weiter' geöffnet
	//Überprüft, ob alle Parameter im Post übergeben wurden:
	if ((isset($_POST["inputEmail"])) && (isset($_POST["inputPassword"])) 
		&& isset ($_POST["vorname"]) && isset ($_POST["nachname"]))
    {
		$showModal = true; //Gibt es Input, wird ein Dialogfenster angezeigt.
		$kundenID = checkEmail($_POST["inputEmail"], $db);
		//echo "Kundenid hat den Wert: " . $kundenID . "\n"; //DEBUG
		if( $kundenID != "" ) //Wenn die Email bereits existiert, also die KundenID ein leerer String ist, führe folgendes aus:
		{
			
			$modal_title = "Registrierung nicht möglich.";
			$dialogHTML = 
			"<p>Diese Email-Adresse ist bereits registriert. Bitte Verwenden Sie eine andere Email-Adresse.</p>";
			$modal_footer = false;
			
		}
		else
		{
			if( (checkEmail($_POST["inputEmail"], $db) =="") && insertDb($_POST["inputEmail"], $_POST["nachname"], $_POST["vorname"], $_POST["inputPassword"], $db) ) //Überprüft, ob die Query nicht bereits genau so ausgeführt wurde (also wenn F5 gedrückt wurde. Speichert anschließend die Werte in der DB ein.
			{
				
				$dialogHTML = checkInsertion($_POST["inputEmail"], $_POST["nachname"], $_POST["vorname"], $_POST["inputPassword"], $db);
				if ($dialogHTML == "")
				{
					$modal_title = "Registrierung nicht erfolgreich!";
					$modal_footer = false;
				}
				else
				{
					$kundenID = checkEmail($_POST["inputEmail"], $db);	
					//echo "Kundenid hat den Wert: " . $kundenID . "\n"; //DEBUG					
					$privateKey = "". bin2hex(openssl_random_pseudo_bytes ( 512 )); //1024-Zeichen langen Key erstellen
					//echo "Der Privatkey ist:\n" . $privateKey . "\n"; //DEBUG
					//echo "\nDer Publickey ist:\n" .  openssl_digest ( $privateKey, "sha512" ) . "\n\n";//DEBUG //128 Zeichen langer Public Key für die Datenbank generieren
					storeKeys( $kundenID , $privateKey, $db );
					welcomeMail( base64_decode($_POST["inputEmail"]) , $activationURL . $privateKey  ); //Willkommens-Email generieren. Diese enthält die URL, die den Account aktivieren kann.
					$modal_title = "Registrierung erfolgreich!";
					$modal_footer = true;
				}
				
			}
	
		}
    }
	
	function storeKeys( string $kundenID , string $privateKey ,  mysqli $db)
	{
		$query = "INSERT INTO
				accountactivation ( KUNDE_ID , EXPIRY , ACTIVATION_KEY )
			VALUES
				(
				'".mysqli_real_escape_string( $db , $kundenID)."',
				DATE_ADD(CURRENT_DATE(), ".mysqli_real_escape_string( $db, VALIDITY_TIMESPAN).") ,
				SHA2( '" .mysqli_real_escape_string( $db , $privateKey )."', 512 )
				);";
		//echo "Query hat den Stringwert:\n" . $query . "\nFühre Query jetzt aus.";
		return ($db->query($query));			
	}
	
	
	function checkEmail( string $b64email, mysqli $db)
	{
		$query =
		"SELECT ID
		FROM
			kunde
		WHERE
			EMAIL =  FROM_BASE64('".mysqli_real_escape_string( $db , $b64email)."')
		";
		//Das Ergebnis $result ist ein Objekt der Klasse mysqli_result. Diese implementiert das Interface Traversible,
		//ist daher traversierbar ähnlich eines Iterators.
		if($result = $db->query($query) )
			//Prüft, ob die Query nicht leer ist
			if ( ($result->num_rows) > 0)
			{
				return sprintf ( "%s" , $result -> fetch_row()[0] ) ;				
				//Dies alles ist obsolet, da nicht mehr der Count der User, sondern die ID des gefundenen Users ausgegeben wird.
				//Sucht aus dem Ergebnisfeld das erste Subfeld(also die erste Zeile).
				//Dort steht das SQL-Count-Ergebnis. Gibt nun davon den
				//ersten Wert aus. Ist dieser 1, so existiert diese Email.
				//$amount = sprintf ( "%s" , $result -> fetch_row()[0] ) ; //Erste Zeile,
				//return !($amount=="0");//Gibt true zurück, wenn die Mail nicht genau 0 mal existiert.
			}
		return ""; // Gibt leeren String zurück, wenn keine User mit dieser Email existieren.
		
	}
	
	function insertDb( string $b64email, string $b64nachname, string $b64vorname, string $password, mysqli $db)
	{
		//Fügt die aus der Anfrage in die Datenbank ein
		$query =
		"INSERT INTO
			kunde (NAME, VORNAME, PASSWORD, EMAIL, EXPIRY)
		VALUES
			(
			FROM_BASE64('".mysqli_real_escape_string( $db , $b64nachname)."'),
			FROM_BASE64('".mysqli_real_escape_string( $db , $b64vorname)."'),
			SHA2('".mysqli_real_escape_string( $db , $password)."', 512),
			FROM_BASE64('".mysqli_real_escape_string( $db , $b64email)."'),
			DATE_ADD(CURRENT_DATE(), ".mysqli_real_escape_string( $db , VALIDITY_TIMESPAN).") 
			)";
		return ($db->query($query));//gibt true aus wenn die query erfolgreich war, false, wenn nicht.
	}
				
	//Überprüft, ob der User erstellt wurde und erzeugt daraus einen String für das Bootstrap Modal
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
    <meta name="description" content="Loginfenster für das Onlinebanking">
    <meta name="author" content="Fabian Konrad">

    <title>Neuen Benutzer registrieren</title>
	
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
				echo "$('#inputContainer').hide();";
				echo "\ndocument.getElementById(\"inputContainer\").style.display = \"none\";";
				echo "$('#dialogModal').show()";
			}
			else
			{
				echo "document.getElementById(\"inputContainer\").style.display = \"block\";\n\n";
				echo "$('#inputContainer').show();\n";
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
				grecaptcha.execute('6LcWtYoUAAAAACHMaAGub1Kh7nTslRGZMGreg7X5', {action: 'register'})
				.then(function(token) 
					{//Captcha-Request an captcha.php
					    var captchaVerificationConnector = new XMLHttpRequest();
							captchaVerificationConnector.onreadystatechange = function() {
								if (this.readyState == 4 && this.status == 202)//Zurückgesendet und HTTP202 Header
								{
									if(this.responseText == "succ=1")
									{										
										document.getElementById("inputContainer").style.display = "none";
										var vn = document.getElementsByName("vorname")[0];
										vn.value = utoa(vn.value);
										var nn = document.getElementsByName("nachname")[0];
										nn.value = utoa(nn.value);
										var em = document.getElementsByName("inputEmail")[0];
										em.value = utoa(em.value); //Email Base64 codieren
										//alert("nun wird der Post ausgeführt");//DEBUG
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
      <div class="card card-register mx-auto mt-5">
        <div class="card-header">Neuen Benutzer registrieren</div>
        <div class="card-body">
          <form name="myInput" method="post">
            <div class="form-group">
              <div class="form-row">
                <div class="col-md-6">
                  <div class="form-label-group">
                    <input type="text" id="firstName" name="vorname" class="form-control" placeholder="Vorname" required="required" autofocus="autofocus">
                    <label for="firstName">Vorname</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-label-group">
                    <input type="text" id="lastName" name="nachname" class="form-control" placeholder="Nachname" required="required">
                    <label for="lastName">Nachname</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="form-label-group">
                <input type="email" id="inputEmail" name="inputEmail" class="form-control" placeholder="Email" required="required">
                <label for="inputEmail">Email</label>
              </div>
            </div>
            <div class="form-group">
              <div class="form-row">
                <div class="col-md-6">
                  <div class="form-label-group">
                    <input type="password" id="inputPassword" name="inputPassword" class="form-control" placeholder="Passwort" required="required">
                    <label for="inputPassword">Passwort</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-label-group">
                    <input type="password" id="confirmPassword" class="form-control" placeholder="Passwort bestätigen" required="required">
                    <label for="confirmPassword">Passwort bestätigen</label>
                  </div>
                </div>
              </div>
            </div>
            <input class="btn btn-primary btn-block" disabled = "true" value="<?php echo $registerstring ?>" onclick="prepareSubmit()"/>
          </form>
          <div class="text-center">
            <a class="d-block small mt-3" href="login.php">Zum Login</a>
            <a class="d-block small" href="forgot-password.php">Passwort vergessen...</a>
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
			<button type="button" class="btn btn-secondary" onclick="modalInputChanger()" 	style="display:<?php echo $modal_footer?"none":"block" ?>">Schließen</button>
			<button type="button" class="btn btn-primary" 	onclick="nextPage()" 			style="display:<?php echo $modal_footer?"block":"none" ?>">Weiter</button>
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
