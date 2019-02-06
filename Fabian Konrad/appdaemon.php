<?php
	//Author: Fabian Konrad
	//FHWS Programmierprojekt
	//Daemon für den Mobile-Agent. Wird mit Post-Paketen angesteuert. Erhält x-www-form-encoded und gibt Json zurück
	
	//Expo Server SDK einbinden
	//require_once __DIR__.'/vendor/autoload.php';
	
	//Datenbankskript einbinden
	//include 'incl/dbconnect.php';
	
	//Pushservices einbinden
	include 'pushservice.php';
	
	//Kommunikationsvariablen/-defines
	
	//Dieses KommunikationsEnum wird bei der Mobile Application analog zu diesem implementiert.
	/**** Leider funktionieren Enums nur mit PECL, was jedoch nicht mehr funktioniert. Daher wurden sie durch Defines ersetzt.
	class QueryMode extends SplEnum { //Querymodus als Enum durcharbeiten
    const __default = self::Error;
	const Success = 0;
    const Error = -1;
    const NewLogin = 1;
	}//Bei Funktionsfähigkeit der Enums müssen lediglich bei den Implementierungen der Defines die _ durch :: ersetzt werden, sowie die Defines selbst gelöscht werden.
	******************/
	define ( "QueryMode_Success"  , 0 );
	define ( "QueryMode_Error"    ,-1 );
	define ( "QueryMode_NewLogin" , 1 );
	define ( "QueryMode_LoginToken" , 2);
	define ( "QueryMode_GetMasterData", 3);
	define ( "QueryMode_ActivatePush", 4);
	define ( "QueryMode_KillSession" , 100);
	
	//Datenvariablen
	
	$response = new \stdClass(); //Die Antwort wird in form eines Jsons durchgearbeitet, weil dies in js einfacher zu implementieren ist.
	

	if( ($_SERVER["REQUEST_METHOD"] === "POST") && ( isset($_POST) ) )
	{
		include 'incl/dbconnect.php';
		//include 'login.php'; //Login importieren mit dessen Methode tryAuth
		//$answer = "mode="; //Der Antwortstring // obsolet, da ja ein Json erstellt werden soll.
		$response->mode = QueryMode_Error;
		if (!(array_key_exists("mode",$_POST)) )
		{
			$response->mode = QueryMode_Error ; //mode-Flag nicht gesetzt -> Error ausgeben; kann nicht in das Switch-Gepackt werden, weil es einen Fehler gäbe, wenn dieser Schlüssel in der Query nicht existiert.
		}
		else
		{
			switch ($_POST["mode"])
			{
				case "" . QueryMode_NewLogin : //String 1
					
					if ( array_key_exists("pw",$_POST) && array_key_exists("id",$_POST) )
					{	
						//echo "Die Array-Keys existieren...\n"; //DEBUG
						if(tryAuth($_POST["id"], $_POST["pw"], $db))
						{
							$response->mode =  QueryMode_LoginToken;
							$response->sessionKey = createAppSession ( $_POST["id"], $db);
						}
						else
						{
							$response->mode = QueryMode_Error;
						}
					}
					else
					{
						$response->mode = QueryMode_Error;
					}
					break;
				case "". QueryMode_LoginToken : //String 2
					if ( array_key_exists("id",$_POST) && array_key_exists("sessionKey",$_POST) )
					{							
						if(authenticateUser($_POST["id"],$_POST["sessionKey"],$db ))
						{
							$response->mode =  QueryMode_Success;
							
						}
						else
						{
							$response->mode = QueryMode_Error;
						}
					}
					else
					{
						$response->mode = QueryMode_Error;
					}
					break;
				case "". QueryMode_GetMasterData : //String 3
					//$response->debugString = "Mode 3 erkannt."; //DEBUG
					if ( array_key_exists("id",$_POST) && array_key_exists("sessionKey",$_POST) )
					{	
						$kundenID = $_POST["id"];
						//$response->debugString .= " Die Array-Keys existieren."; //DEBUG
						if(authenticateUser( $kundenID,$_POST["sessionKey"],$db ))
						{
							//$response->debugString .= "User Erfolgreich authentifiziert."; //DEBUG
							$masterData = queryMasterData($kundenID , $db);
							if ( $masterData != "") // Wenn die Query keinen leeren String zurückgibt, also fehlerhaft ist:
							{
								//$response->debugString .= "speichere die Stammdaten ein:.". $masterData; //DEBUG
								$response->masterData = $masterData;
								$response->mode =  QueryMode_Success;
							}
							else
							{
								//$response->debugString .= "Fehler beim Auslesen der Stammdaten."; //DEBUG
								$response->mode = QueryMode_Error;
							}
						}
						else
						{
							$response->mode = QueryMode_Error;
						}
					}
					else
					{
						$response->mode = QueryMode_Error;
					}
					break;
				case "". QueryMode_ActivatePush : //String 4
					if ( array_key_exists("id",$_POST) && array_key_exists("sessionKey",$_POST) && array_key_exists("token",$_POST))
					{							
						if(authenticateUser($_POST["id"],$_POST["sessionKey"],$db ))
						{
						//$response->debugstring = "User erfolgreich authentifiziert. "; //DEBUG
							$token = $_POST["token"];
						//$response->debugstring .= "Token hat den Wert:" . $token . "\t";//DEBUG
						if(storeToken( $token , $_POST["id"], $db ) )
							{
								//Der Expo-Push-Server benötigt eine einzigartige Kennzeichnung des Clients, an den die Nachricht geschickt werden soll. Dies wird über den SHA512-Hash des App-SessionKeys bewerkstelligt.
								registerPushClient(openssl_digest($_POST["sessionKey"] , "SHA512") , $token );
								$response->mode =  QueryMode_Success;
								
							}
							else
							{
								$response->mode= QueryMode_Error;
							}
						}
						else
						{
							$response->mode = QueryMode_Error;
						}
					}
					else
					{
						$response->mode = QueryMode_Error;
					}
					break;
				case "". QueryMode_KillSession: //String 100
					if ( array_key_exists("id",$_POST) && array_key_exists("sessionKey",$_POST) )
					{	
						$sessionKey = $_POST["sessionKey"];						
						$kundenID = $_POST["id"];
						if(authenticateUser( $kundenID , $sessionKey, $db ))
						{
							 $response->mode =  killSession( $kundenID , $sessionKey , $db)?QueryMode_Success:QueryMode_Error; //Success zurückgeben, wenn der User ausgeloggt wurde, Error wenn es in der Query Fehler gab.
						}
						else
						{
							$response->mode = QueryMode_Error;
						}
					}
					else
					{
						$response->mode = QueryMode_Error;
					}
					break;
				default:
					$response->mode = QueryMode_Error;
					
			}
		}
		//$answer .= $mode; //Wurde nur fuer die Verwendung von x-www-form-encoded benoetigt.
			header('Content-Type: application/json; charset=UTF-8' );
			if( $response->mode == QueryMode_Error )
			{
				header('HTTP/1.0 406 Not Acceptable');
			}
			echo json_encode($response);
	}
	else
	{
		header('HTTP/1.0 404 Not Found');
		echo "<h1>404 Not Found</h1>";
	}
	
	
	//Funktionen
	function storeToken(string $token, string $kundenID, mysqli $db)
	{
		$clear_query = "UPDATE appsession
						SET PUSHTOKEN = 'NO_TOKEN'
						WHERE PUSHTOKEN = '".mysqli_real_escape_string( $db , $token)."';
						";
		$store_query = "UPDATE appsession
						SET PUSHTOKEN = '".mysqli_real_escape_string( $db , $token)."'
						WHERE KUNDE_ID = '".mysqli_real_escape_string( $db , $kundenID)."';
						"; //Alle Existenzen dieses Tokens löschen, dann dieses Token dieser kundenID zuweisen.
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
	function tryAuth(string $inputEmail , string $inputPassword, mysqli $db) // Kopiert aus login.php
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
		
	function createAppSession(string $kundenID, mysqli $db)
	{//Erstellt einen neuen Datenbankeintrag mit dem SHA512 Wert einer Zufallszahl als Eintrag in der Datenbank. Gibt die Zufallszahl, also den generierten PrivateKey, anschließend zurück.
		$privateSesKey = bin2hex(openssl_random_pseudo_bytes(512));
		$sessionQuery = "REPLACE INTO appsession
							( KUNDE_ID , SESSION_KEY )
						VALUES
							(
								'".mysqli_real_escape_string( $db , $kundenID)."' ,
								SHA2('".mysqli_real_escape_string( $db , $privateSesKey)."', 512)
							);";
		return ($db->query($sessionQuery))
			?
				$privateSesKey
			:
				trigger_error("Fehler in der Query");
	}
	
	function authenticateUser(string $kundenID, string $privateKey , mysqli $db ) //SessionKey der App verifizieren.
	{
		$authsuccess = false;//Authentifizierung war noch nicht erfolgreich
		$auth_query = "SELECT COUNT(*)  
			FROM 
				appsession
			WHERE 
				KUNDE_ID = '".mysqli_real_escape_string( $db , $kundenID)."'
				AND
				SESSION_KEY =  SHA2('".mysqli_real_escape_string( $db , $privateKey)."', 512)		
			;";
		if( $result = $db->query($auth_query) )
		{
			//$loginstring = "Query eingelesen. Anzahl Zeilen: ". $result -> num_rows; // DEBUG
			//Prüft, ob die Query nicht leer ist
			if ($result->num_rows)
			{
				$amount = sprintf ( "%s" , $result -> fetch_row()[0] ) ; //Erste Zeile, erste Spalte (also der Countwert) wird ausgelesen und in String geparst.
				$authsuccess = ($amount=="1"); //Setzt Den Parameter für erfolgreichen login auf true, falls die Count-Zahl 1 ist.
			}
		}
		return $authsuccess;
	}
	
	function killSession( string $kundenID, string $sessionKey, mysqli $db )
	{ //Löscht den Eintrag in der Sessiontabelle und gibt anschließend bei Erfolg true zurück.
		removePushClient( $sessionKey);
		$delete_query  = "DELETE FROM appsession
					WHERE 
						KUNDE_ID = '".mysqli_real_escape_string( $db , $kundenID)."'
					;";
		return $db->query($delete_query);
	}
	
	function queryMasterData (string $kundenID, mysqli $db )
	{ //Sucht die Stammdaten(Vorname und Nachname) eines spezifischen Users heraus
		$returnstring = ""; //Leeren Ergebnisstring generieren.
		$md_query  ="SELECT VORNAME , NAME
						FROM kunde
					WHERE 
						ID = '".mysqli_real_escape_string( $db , $kundenID)."'
					;";
		//global $response;	//DEBUG		
		//$response -> debugString .= "Die Query hat den Inhalt: " . $md_query ; //DEBUG
		if ( $result = $db -> query($md_query) ) //Query ausführen
		{
			$row = $result -> fetch_row(); //Erste Zeile als Array abrufen
			//$response -> debugString .= " ROW[0] hat den Wert: " . $row[0] . " Row[1] hat den Wert: " . $row[1] ; //DEBUG
			$returnstring .= "". $row[0] . " " . $row[1] ; // Zeile in den String Vorname Name umwandeln
		}
		//$response -> debugString .= " Returnstring hat den Wert: " . $returnstring ; //DEBUG
		return $returnstring;
	}
	?>