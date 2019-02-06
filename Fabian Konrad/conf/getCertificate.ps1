#Root-Zertifikat ausstellen und direkt signieren
openssl req -x509 -newkey rsa:4096 -keyout "./ssl.key/ca-bundle.key" -out "./ssl.crt/ca-bundle.crt" -days 3650 -config certify_server.cnf -sha512 -nodes
openssl rsa -in "./ssl.key/ca-bundle.key" -out "./ssl.key/ca-public.key"
"" >"./ssl.crt/serial.srl"
$str  = openssl x509 -in "./ssl.crt/ca-bundle.crt" -noout -serial
$str = ($str).Substring( 7 , 16 )
$str > "./ssl.crt/serial.srl" #Serialnummer in die Datei einfügen. Die Deklaration muss vorher aus dem String entfernt werden.
openssl req -x509 -newkey rsa:4096 -keyout "./server-ca.key" -out "./server-ca.crt" -days 3650 -config certify_server.cnf -sha512 -nodes
#Certificate Signing Request aus drei zufälligen Schlüsseln generieren
openssl req -new -newkey rsa:4096 -keyout "./ssl.key/server0.key" -out "./ssl.csr/cert0.csr" -days 3650 -config certify_server.cnf -sha512 -nodes
openssl req -new -newkey rsa:4096 -keyout "./ssl.key/server1.key" -out "./ssl.csr/cert1.csr" -days 3650 -config certify_server.cnf -sha512 -nodes
openssl req -new -newkey rsa:4096 -keyout "./ssl.key/server2.key" -out "./ssl.csr/cert2.csr" -days 3650 -config certify_server.cnf -sha512 -nodes
#Public Keys generieren
openssl rsa -in "./ssl.key/server0.key" -out "./ssl.key/pub0.key"
openssl rsa -in "./ssl.key/server1.key" -out "./ssl.key/pub1.key"
openssl rsa -in "./ssl.key/server2.key" -out "./ssl.key/pub2.key"
#Zertifikate mit dem Root-Zertifikat ausstellen
openssl x509 -req -CA "./ssl.crt/ca-bundle.crt" -CAkey "./ssl.key/ca-bundle.key" -in "./ssl.csr/cert0.csr" -out "./ssl.crt/cert0.crt" -days 3650 -CAcreateserial #-CAserial "./ssl.crt/serial.srl"
openssl x509 -req -CA "./ssl.crt/ca-bundle.crt" -CAkey "./ssl.key/ca-bundle.key" -in "./ssl.csr/cert1.csr" -out "./ssl.crt/cert1.crt" -days 3650 -CAcreateserial #-CAserial "./ssl.crt/serial.srl"
openssl x509 -req -CA "./ssl.crt/ca-bundle.crt" -CAkey "./ssl.key/ca-bundle.key" -in "./ssl.csr/cert2.csr" -out "./ssl.crt/cert2.crt" -days 3650 -CAcreateserial #-CAserial "./ssl.crt/serial.srl"
#Zertifikate mit dem Let's Encrypt - Zertifikat signieren
#openssl x509 -req -CA "./ssl.crt/sslforfree.crt" -CAkey "./ssl.key/sslforfree.key" -in "./ssl.csr/cert0.csr" -out "./ssl.crt/cert0.crt" -CAcreateserial
#openssl x509 -req -CA "./ssl.crt/sslforfree.crt" -CAkey "./ssl.key/sslforfree.key" -in "./ssl.csr/cert1.csr" -out "./ssl.crt/cert1.crt" -CAcreateserial
#openssl x509 -req -CA "./ssl.crt/sslforfree.crt" -CAkey "./ssl.key/sslforfree.key" -in "./ssl.csr/cert2.csr" -out "./ssl.crt/cert2.crt" -CAcreateserial