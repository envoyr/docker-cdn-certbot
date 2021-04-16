<?php
// TODO: set credentials
$pdo = new PDO('mysql:host=localhost;dbname=databasename', 'username', 'password');

$sleep = 60;
$email = "cdn-certbot@envoyr.com";

echo "Certbot worker started!";

// start worker
while (true) {
    $statement = $pdo->prepare("SELECT * FROM domains WHERE ssl = ? AND ssl_certificate = ?");
    $statement->execute([true, null]);

    // obtain new certificates
    while($row = $statement->fetch()) {
        $domain = $row['domain'];

        // TODO: dns lookup to point at "username.cdn.envoyr.com"

        echo "Obtain new certificate for domain: {$domain}\n";
        shell_exec("certbot certonly --standalone -d {$domain} --non-interactive --agree-tos --email {$email} --http-01-port=8888");
    }
	
    // TODO: delete certificate if ssl = false

    // sleep
    echo "Go to sleep for {$sleep} seconds now! Bye";
    sleep($sleep);
}
