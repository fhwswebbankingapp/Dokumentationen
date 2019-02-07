<?php
	//Author = Fabian Konrad
	//			Programmierprojekt FHWS
	
	//Vom Client im Request-String übergebener Key, der im Captcha erzeugt wurde.
	//Test ob der Key im Header ist, sonst HTTP400 im Else-Teil ausgeben
	if( array_key_exists(  "ukey" , $_POST) )
	{
		if ( $userkey = $_POST["ukey"] )
		{
		//Privates Token und Serveradresse für die Authentifizierung bei der Google Captcha Api
		$privateToken = "6LcWtYoUAAAAAGaZXDFVv7wepmyc_bj5nij-DTLI";
		$url = "https://www.google.com/recaptcha/api/siteverify";
		
		if( ($userkey!= null) && ($userkey!="") )
		{
			$param_field = 'secret=' . $privateToken . '&response=' . $userkey; 
			//POST über cURL
			$server_query = curl_init( $url);
			curl_setopt( $server_query, CURLOPT_POST, 1); //HTTP-Request als POST ausführen
			curl_setopt( $server_query, CURLOPT_POSTFIELDS, $param_field); //Parameter ergänzen
			curl_setopt( $server_query, CURLOPT_FOLLOWLOCATION, 1); //Location-Header verfolgen
			curl_setopt( $server_query, CURLOPT_HEADER, 0); //Header nicht in die Ausgabe packen
			curl_setopt( $server_query, CURLOPT_RETURNTRANSFER, 1); //Rückgabe in String packen auf true setzen
			
			
			$response = curl_exec( $server_query );
			curl_close($server_query);
			header('HTTP/1.0 202 Accepted');
			$response_field = json_decode( $response, true);
			echo "succ=" . $response_field["success"];	
		}
		else
		{
		header('HTTP/1.0 403 Forbidden');
		echo "succ=0";
		}
		}
	}
	else
	{
		header('HTTP/1.0 400 Bad Request');
		echo "succ=0";
	}
?>