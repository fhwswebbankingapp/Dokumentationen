<?php
		//Expo Server SDK einbinden
		require_once __DIR__.'/vendor/autoload.php';
		//Datenbankskript einbinden
		include 'incl/dbconnect.php';
		
		session_start();
		
		
		$response = "";
	if( isset($_SESSION["id"]) ) //um den entsprechenden User zu finden, wird bisher noch die ID verwendet. Dies soll durch die Session ersetzt werden.
	{
		$kundenID = $_SESSION["id"];
		//echo "kundenID ist: " .$kundenID; //DEBUG
		if ( userExistance($kundenID, $db) )
		{
			$certificate = openssl_digest(openssl_random_pseudo_bytes(512), "SHA512" );//Zufälliges Shared Secret generieren. Länge 128 Zeichen
			if( storeSecondToken($certificate , $kundenID , $db) )
			{
				//QR-Code über Api beziehen:
				//include "pecl_http-3.2.0.ext.phar";
				//Das Zertifikat als String, der in den QR-Code eingebunden werden muss.		
				$url = "https://localhost/qrcode.php";
				
				//$handle = fopen( "php://temp/maxmemory:500000" , "r+" ); //DEBUG Stream nach stdout schieben
				//$handle = "";
				
				$param_field = "c=" . $certificate;
				// echo $url . $param_field; //DEBUG
				$server_query = curl_init( $url);
				//curl_setopt( $server_query, CURLOPT_VERBOSE, true); //DEBUG Curl in den Errorstream schreiben lassen
				//curl_setopt( $server_query, CURLOPT_STDERR, $handle ) ; //DEBUG
				curl_setopt( $server_query, CURLOPT_POST, 1); //HTTP-Request als POST ausführen
				curl_setopt( $server_query, CURLOPT_POSTFIELDS, $param_field); //Parameter ergänzen
				//curl_setopt( $server_query, CURLOPT_FOLLOWLOCATION, 1); //Location-Header verfolgen
				curl_setopt( $server_query, CURLOPT_HEADER, 0); //Header nicht in die Ausgabe packen
				curl_setopt( $server_query, CURLOPT_RETURNTRANSFER, 1); //Rückgabe in String packen auf true setzen
				//curl_setopt( $server_query, CURLOPT_HEADER, false);
				curl_setopt( $server_query, CURLOPT_CONNECTTIMEOUT, 20);
				//curl_setopt( $server_query, CURLOPT_USERAGENT,'softvar'); // your useragent name
				//curl_setopt( $server_query, CURLOPT_RETURNTRANSFER, true);
				//curl_setopt( $server_query, CURLOPT_HTTPHEADER,array('Accept: imgage/png', "Content-type: application/x-www-form-urlencoded"));
				//curl_setopt( $server_query, CURLOPT_FAILONERROR, FALSE);
				curl_setopt( $server_query, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt( $server_query, CURLOPT_SSL_VERIFYHOST, FALSE);
				//curl_setopt( $server_query, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt( $server_query, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
				
				
				$response = curl_exec( $server_query );
				curl_close($server_query);
				
				//$debug_string = "";//DEBUG
				//rewind( $handle ); //DEBUG
				/* //File aus dem RAM in String laden
				while (! feof ($handle)) //DEBUG
				{ 
					echo fgets($handle) . "</br>"; //Errorlog von curl ausgeben
				} 
		
				fclose($handle); //DEBUG
				*/
				//$response_keys = array_keys($response);//DEBUG
				
				//Curl-Responsefeld auslesen
				/*
				if($response) //DEBUG
				{
					foreach ( $response as $key => $value   ) //DEBUG 
					{//DEBUG
					$debug_string .= $value;//DEBUG
					}
					echo "Response: " . $debug_string . "\nResponse hat den Wert: " . $response; //DEBUG
				}
				else //DEBUG
				{
					echo "Query gescheitert. Response hat den Wert: " . $response;;//DEBUG
				}
				*/
				
				
				//Das Bild über HTTP GET beziehen
				//$response = http_get($url . "?c=" . $certificate);
			}
		}
	}
	
	
	
	
	//Funktionen für Datenbankzugriffe
	
	function storeSecondToken(string $certificate, string $kundenID, mysqli $db)
	{
		$clear_query = "UPDATE appsession
						SET SECOND_SECRET = 'NO_TOKEN'
						WHERE SECOND_SECRET = '".mysqli_real_escape_string( $db , $certificate)."';
						"; //Shared Secret nicht zweimal vergeben.
		$store_query = "UPDATE appsession
						SET SECOND_SECRET = '".mysqli_real_escape_string( $db , $certificate)."'
						WHERE KUNDE_ID = '".mysqli_real_escape_string( $db , $kundenID)."';
						";//Alle Existenzen dieses Tokens löschen, dann dieses Token dieser kundenID zuweisen, jedoch nur wenn für sie bereits eine Session in der Datenbank hinterlegt wurde.
		//global $response;//DEBUG
		//$response->debugstring .= $clear_query . $store_query;//DEBUG
		if($db ->query($clear_query))
		{
			//$response->debugstring .= "\tClearquery war korrekt"; //DEBUG
		}
		if($db->query($store_query))
		{
			//$response->debugstring .= "\tResponsequery war auch korrekt"; //DEBUG
			return true;
		}
	}
	
	function userExistance( string $kundenID, mysqli $db )
	{ //Löscht den Eintrag in der Sessiontabelle und gibt anschließend bei Erfolg true zurück.
		$authsuccess = false;
		$id_query  = "SELECT COUNT(*)
						FROM appsession
						WHERE 
						KUNDE_ID = '".mysqli_real_escape_string( $db , $kundenID)."'
					;";
			if( $result = $db->query($id_query) )
			{
				if ($result->num_rows)
				{
					$amount = sprintf ( "%s" , $result -> fetch_row()[0] ) ; //Erste Zeile, erste Spalte (also der Countwert) wird ausgelesen und in String geparst.
					$authsuccess = ($amount=="1"); //Setzt Den Parameter für erfolgreichen login auf true, falls die Count-Zahl 1 ist.
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
    <meta name="title" content="Zertifikat Beziehen">
    <meta name="author" content="Programmierprojekt/Fabian Konrad">

    <title>Zertifikat Beziehen</title>

    <!-- Bootstrap core CSS-->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin.css" rel="stylesheet">
	<style>
	.card-qrcode{
		
		width: 60rem;
		max-width: 80%;
	}
	
	</style>
	<script>
		
	</script>
  </head>

  <body class="bg-dark">

    <div class="container">
	
      <div class="card card-qrcode mx-auto m-5" >
	  
        <img class="card-img-top" id="qrcode" src="<?php echo $response ?>" alt="Fehler bei der Datenübertragung">
		<div class="card-header text-center">
		<h4>Ihr Zertifikat als QR-Code:</h4>
		</div>
        <div class="card-body">
          <div class="text-center mb-4">
            <p>Melden Sie sich mit Ihrer Email-Adresse und Ihrem Passwort auf dem Mobile Agent an. Mit dem Scan dieses QR-Codes wird Ihr Zertifikat auf das Mobilgerät übertragen.</p>
          </div>
          <form>
            <a class="btn btn-primary btn-block" href="/index.php">Zur Homepage zurück</a>
            <a class="btn btn-secondary btn-block" onclick="location.reload(true)">Neues Zertifikat generieren</a>
          </form>
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