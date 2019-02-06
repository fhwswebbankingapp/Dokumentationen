<?php
	//Author: Fabian Konrad
	//FHWS Programmierprojekt
	//Kommunikationstool für die Pushservices
	
	//Expo Server SDK einbinden
	require_once __DIR__.'/vendor/autoload.php';
	//Datenbankskript einbinden
	//include 'incl/dbconnect.php';
	
	// Die Expo-Instanz initialisieren...
	$expo = \ExponentPhpSDK\Expo::normalSetup();
	
function registerPushClient( string $geraeteName , string $geraeteID )
{ //Diese Funktion registriert den Client beim Expo-Pushserver
	//$response->debugstring .= "Token eingespeichert ";
	$interestDetails = [ $geraeteName , $geraeteID]; //Session-ID in Expos Pushtoken-Datenbank speichern.
	
	// Die Expo-Instanz initialisieren... , sie soll jedoch nur initialisiert werden, wenn sie wirklich gebraucht wird.
	//$expo = \ExponentPhpSDK\Expo::normalSetup();
	
	// Subscribe the recipient to the server
	global $expo;
	$expo->subscribe($interestDetails[0], $interestDetails[1]);
	
	// Build the notification data
	//Hier wird die Nachricht nun generiert, der User weird darauf hingewiesen, dass er nun beim  Push-Dienst angemeldet ist.
	//$notification = ['title' => 'Registrierung zum Pushmessagedienst erfolgreich.','body' => 'Herzlich Willkommen beim Message-Service des Onlinebankings. Mit dieser Nachricht bestätigen wir Ihre Anmeldung für die Push-Notifications.'];
	
	// Notify an interest with a notification
	// Benachrichtigung nun an den Kunden versenden
	//$expo->notify($interestDetails[0], $notification);
	
	
	//Client benachrichtigen über die Funktion notifyClient()
	notifyClient($interestDetails[0], 'Registrierung zum Pushmessagedienst erfolgreich.', 'Herzlich Willkommen beim Message-Service des Onlinebankings. Mit dieser Nachricht bestätigen wir Ihre Anmeldung für die Push-Notifications.');
}

function notifyClient( string $geraeteName, string $title , string $body )
{ //Diese Funktion sendet eine Nachricht an den User mit der Session-ID $geraeteName . Ihr Inhalt wird durch die Parameter $title und $body bestimmt.
	global $expo;
	$notification = ['title' => $title ,'body' => $body];
	$expo->notify($geraeteName , $notification );
	
}

function removePushClient( string $geraeteName )
{//löscht ein Handy wieder aus der Push-Datenbank von Expo.
	global $expo;
	$expo->unsubscribe($geraeteName);
}