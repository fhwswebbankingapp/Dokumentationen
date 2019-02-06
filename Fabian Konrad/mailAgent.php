<?php
/**
 * Author: Programmierprojekt/Fabian Konrad
   Basierend auf dem Beispiel für den PHPMailer auf Github werden die folgenden Methoden generiert.
   Zusätzlich ist hier der komplette HTML-Text hard-coded inkludiert.
   This example shows settings to use when sending via Google's Gmail servers.
 * This uses traditional id & password authentication - look at the gmail_xoauth.phps
 * example to see how to use XOAUTH2.
 * The IMAP section shows how to save this message to the 'Sent Mail' folder using IMAP commands.
 */
//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
require __DIR__.'//vendor/autoload.php';
//Create a new PHPMailer instance

function welcomeMail (string $targetAdress , string $activateLink )
{ //Diese Funktion generiert einen Text für einen neune Kunden und beinahaltet einen Link zur Aktivierung des Accounts.
		sendGmail( $targetAdress,
			"Willkommen beim Webbanking" ,
			mailHTMLHeader().
			mailHTMLBody(
				"Willkommen bei unserem Onlinebanking-Tool.",
				$activateLink,
				"Account aktivieren",
				"Wir wünschen Ihnen viel Spaß mit unserem Tool."
				));
}
function recoveryMail( string $targetAdress , string $recoveryLink )
{ //Diese Funktion generiert einen Text für ein Passwort-Recovery und sendet ihn ab.
		sendGmail( $targetAdress,
			"Sie haben erfolgreich Ihr Passwort zurückgesetzt." ,
			mailHTMLHeader().
			mailHTMLBody( 
				"Sie haben Ihr Passwort auf unserer Seite zurückgesetzt. Mit einem Klick auf diesen Button können Sie sich ein neues Passwort zuweisen." ,
				$recoveryLink ,
				"Passwort zurücksetzen",
				"Geben Sie einfach das neue Passwort im Passworteingabefeld ein."
			));		
}
function mailHTMLBody($firstMessage ,  $buttonURL , $buttonText , $secondMessage ) 
{	//Generiert einen Body der Nachricht, man kann hierbei drei Textfelder und einen Link selbst auswählen.
		 return
						"<body class=\"\" style=\"background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\">
							<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"body\" style=\"border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;\">
								<tr>
								<td style=\"font-family: sans-serif; font-size: 14px; vertical-align: top;\">&nbsp;</td>
								<td class=\"container\" style=\"font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;\">
								<div class=\"content\" style=\"box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;\">
				
								<!-- START CENTERED WHITE CONTAINER -->
								<span class=\"preheader\" style=\"color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;\">
			Sehr geehrter Kunde, " . $firstMessage ."					
								</span>
								<table class=\"main\" style=\"border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;\">
							
								<!-- START MAIN CONTENT AREA -->
								<tr>
									<td class=\"wrapper\" style=\"font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;\">
									<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;\">
										<tr>
										<td style=\"font-family: sans-serif; font-size: 14px; vertical-align: top;\">
											
											<p style=\"font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;\">Sehr geehrter Kunde,</p>
											<p style=\"font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;\">
			". $firstMessage ."
								</p>
								<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"btn btn-primary\" style=\"border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;\">
								<tbody>
									<tr>
									<td align=\"left\" style=\"font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;\">
										<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;\">
										<tbody>
											<tr>
											<td style=\"font-family: sans-serif; font-size: 14px; vertical-align: top; background-color: #3498db; border-radius: 5px; text-align: center;\"><a href=\"
			". $buttonURL ."
						  \" target=\"_blank\" style=\"display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;\">
			". $buttonText ."
											</a> </td>
											</tr>
										</tbody>
										</table>
									</td>
									</tr>
								</tbody>
								</table>
								<p style=\"font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;\">
								". $secondMessage ."
								</p>
								<p style=\"font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;\">Mit freundlichen Grüßen</p>
								<p style=\"font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;\">Ihr Webbanking-Team</p>
							</td>
							</tr>
						</table>
						</td>
					</tr>
		
					<!-- END MAIN CONTENT AREA -->
					</table>
		
					<!-- START FOOTER -->
					<div class=\"footer\" style=\"clear: both; Margin-top: 10px; text-align: center; width: 100%;\">
					<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;\">
						<tr>
						<td class=\"content-block\" style=\"font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;\">
							<span class=\"apple-link\" style=\"color: #999999; font-size: 12px; text-align: center;\">FHWS Webbanking-Team, Sanderheinrichsleitenweg 20, 97074 Würzburg</span>
							<br>Unsere Hochschule erreichen Sie über diesen Link: <a href=\"http://fiw.fhws.de\" style=\"text-decoration: underline; color: #999999; font-size: 12px; text-align: center;\">FHWS</a>.
							<br>Unser Projekt auf Github: <a href=\"https://github.com/topics/fhwswebbankingapp\" style=\"text-decoration: underline; color: #999999; font-size: 12px; text-align: center;\">Zu den Github-Repositorys</a>.
						</td>
						</tr>
						<tr>
						<td class=\"content-block powered-by\" style=\"font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;\">
							Powered by <a href=\"http://htmlemail.io\" style=\"color: #999999; font-size: 12px; text-align: center; text-decoration: none;\">HTMLemail</a>.
						</td>
						</tr>
					</table>
					</div>
					<!-- END FOOTER -->
		
				<!-- END CENTERED WHITE CONTAINER -->
				</div>
				</td>
				<td style=\"font-family: sans-serif; font-size: 14px; vertical-align: top;\">&nbsp;</td>
			</tr>
			</table>
		</body>
		</html>
";	
}

function mailHTMLHeader()
{//nur aus optischen Gründen. Gibt einfach das CSS aus.
return 
"<!doctype html>
<html>
  <head>
    <meta name=\"viewport\" content=\"width=device-width\">
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
    <title>Simple Transactional Email</title>
    <style>
    @media only screen and (max-width: 620px) {
      table[class=body] h1 {
        font-size: 28px !important;
        margin-bottom: 10px !important;
      }
      table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
        font-size: 16px !important;
      }
      table[class=body] .wrapper,
            table[class=body] .article {
        padding: 10px !important;
      }
      table[class=body] .content {
        padding: 0 !important;
      }
      table[class=body] .container {
        padding: 0 !important;
        width: 100% !important;
      }
      table[class=body] .main {
        border-left-width: 0 !important;
        border-radius: 0 !important;
        border-right-width: 0 !important;
      }
      table[class=body] .btn table {
        width: 100% !important;
      }
      table[class=body] .btn a {
        width: 100% !important;
      }
      table[class=body] .img-responsive {
        height: auto !important;
        max-width: 100% !important;
        width: auto !important;
      }
    }
    /* -------------------------------------
        PRESERVE THESE STYLES IN THE HEAD
    ------------------------------------- */
    @media all {
      .ExternalClass {
        width: 100%;
      }
      .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
        line-height: 100%;
      }
      .apple-link a {
        color: inherit !important;
        font-family: inherit !important;
        font-size: inherit !important;
        font-weight: inherit !important;
        line-height: inherit !important;
        text-decoration: none !important;
      }
      .btn-primary table td:hover {
        background-color: #34495e !important;
      }
      .btn-primary a:hover {
        background-color: #34495e !important;
        border-color: #34495e !important;
      }
    }
    </style>
</head>
";	
}	
function sendGmail ( string $targetAdress , string $subject , string $body )
{
		//Funktion sendet Mail mit dieser Konfiguration an den Kunden.
		//Es werden Zieladresse, Betreff und Inhalt bzw. Body als Parameter benötigt.
		$mail = new PHPMailer;
		$mail->setLanguage('de');
		//Tell PHPMailer to use SMTP
		$mail->isSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 0;
		//Set the hostname of the mail server
		$mail->Host = 'smtp.gmail.com';
		// use
		// $mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = 587;
		//Set the encryption system to use - ssl (deprecated) or tls
		$mail->SMTPSecure = 'tls';
		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;
		//Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = "credentials.fhwswebbankingapp@gmail.com";
		//Password to use for SMTP authentication
		$mail->Password = "sdgfsdfgsdfw435345lsgjSADFDF3sgdgrsASDFE#!#!";
		//Set who the message is to be sent from
		$mail->Encoding = '8bit';
		$mail->CharSet = 'UTF-8';
		$mail->setFrom('credentials.fhwswebbankingapp@gmail.com', 'Webbankingapp Mailservices');
		//Set an alternative reply-to address
		//$mail->addReplyTo('replyto@example.com', 'First Last');
		//Set who the message is to be sent to
		$mail->addAddress($targetAdress, $targetAdress);
		//Set the subject line
		$mail->Subject = $subject;
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		//$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
		//Replace the plain text body with one created manually
		//$mail->AltBody = 'This is a plain-text message body';
		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');
		//send the message, check for errors
		$mail->isHTML(true);
		$mail->Body = $body;
		$mail->altBody = "Diese Nachricht ist ein in UTF-8 codierter HTML-Text.";
		
		return $mail->send();
		//	if ($mail->send()) {
		//		echo "Mailer Error: " . $mail->ErrorInfo;
		//	} else {
		//		echo "Message sent!";
    //Section 2: IMAP
    //Uncomment these to save your message in the 'Sent Mail' folder.
    //if (save_mail($mail)) {
    //   echo "Message saved!";
    //
}




//Section 2: IMAP
//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
//You can use imap_getmailboxes($imapStream, '/imap/ssl') to get a list of available folders or labels, this can
//be useful if you are trying to get this working on a non-Gmail IMAP server.
//function save_mail($mail)
//{
//    //You can change 'Sent Mail' to any other folder or tag
//    $path = "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail";
//    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
//    $imapStream = imap_open($path, $mail->Username, $mail->Password);
//    $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
//    imap_close($imapStream);
//    return $result;
//}

?>