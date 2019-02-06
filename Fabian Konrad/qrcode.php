 <?php
	//require chillerlan\QRCode;
	//Author: Fabian Konrad
	//QR-Code-Abfrage: Erhält Post und gibt ein QR-Code-Bild zurück
    //QR-Code-Api im Ordner phpqrcode einbinden:
	//include ('./phpqrcode/qrlib.php');
	//Andere QR-Code-Api einbinden; muss vorher über Composer installiert werden.
    //include ('./php-qrcode-master/vendor/autoload.php');
	//include ('./php-qrcode-master/src/QRCode.php');
	//require ("vendor/autoload.php");
	use chillerlan\QRCode\{QRCode, QROptions};
	require_once __DIR__.'//vendor/autoload.php';
	//header("Content-Type: text/html");//DEBUG
	//echo "Zeugs" ; //DEBUG
	//QRcode::png("asdf");//DEBUG
	/*foreach (array_keys($_POST) as $key)
	{
		echo $key;
	}*/ //DEBUG-Schleife
	//echo '<img src="'.(new QRCode)->render("Rickrolling").'" />';//DEBUG
	
	//Leeren String erstellen und abhängig von GET oder POST mit Content befüllen
	$cert_string  = "";
	if( array_key_exists(  "c" , $_POST ))
		$cert_string = $_POST["c"]; // Post enthält den Zertifikatstring
	else if( array_key_exists(  "c" , $_GET))
		$cert_string = $_GET["c"];  // GET enthält den Zert-String	
	//Test ob der Key im Header ist, sonst HTTP400 im Else-Teil ausgeben
	if($cert_string != "")
	{
		$options = new QROptions([
		'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
		'eccLevel'     => QRCode::ECC_L,
		'scale'        => 18,
		'imageBase64'  => true
		]);
		

		//Bild als Base64 in den Browser schieben
		//header("Content-Type: image/png");
		//echo 'data:image/png;base64,';
		echo (new QRCode($options))->render($cert_string);
		//QRcode::png($cert_string);
	}
	else
	{
		header('HTTP/1.0 400 Bad Request');
		echo "kein Token übergeben";
	}
?>