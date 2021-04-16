<?php

namespace Envoyr\CDN\Certbot;

use PDO;

class worker
{
    private PDO $pdo;

    private int $sleep;
    private bool $debug;
    private string $email;

    /**
     * Worker constructor
     */
    public function __construct()
    {
        // Set variables
        $this->sleep = getenv('SLEEP') ?: 2;
        $this->debug = getenv('DEBUG') ?: false;
        $this->email = getenv('LETSENCRYPT_EMAIL');

        // Set PDO connection variables
        $hostname = getenv('MYSQL_HOSTNAME');
        $database = getenv('MYSQL_DATABASE');
        $username = getenv('MYSQL_USERNAME');
        $password = getenv('MYSQL_PASSWORD');

        // Initialize PDO
        $this->pdo = new PDO("mysql:host={$hostname};dbname={$database}", $username, $password);
    }

    /**
     * Create new worker instance
     */
    public function init()
    {
        $this->info("Certbot worker started!");

        // start worker
        while (true) {
            $statement = $this->pdo->prepare("SELECT * FROM `domains` WHERE `ssl` = ? AND `ssl_certificate` IS NULL");
            $statement->execute([true]);

            // obtain new certificates
            while ($row = $statement->fetch()) {
                $this->obtain_certificates($row['id'], $row['domain']);
            }

            // TODO: delete certificate if ssl = false & certificate exists

            sleep($this->sleep);
        }
    }

    protected function info($message): void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Obtain new certificate for domain
     *
     * TODO: dns lookup check for cname point at "username.cdn.envoyr.com"
     *
     * @param $id
     * @param $domain
     */
    private function obtain_certificates($id, $domain)
    {
        $this->info("Obtain new certificate for domain: {$domain}");
        exec("certbot certonly --standalone -d {$domain} --non-interactive --agree-tos --email {$this->email} --http-01-port=8888" . ($this->debug ? ' --dry-run' : ''), $output, $result_code);

        if ($result_code === 0 && preg_match("/\/etc\/letsencrypt\/live\/(.*)\/fullchain\.pem/", $output, $matches)) {
            $this->info('Certificate created!');

            // Update domain
            $update = $this->pdo->prepare("UPDATE `domains` SET `ssl_certificate` = ?, `ssl_certificate_key` = ? WHERE `id` = ? AND `ssl_certificate` IS NULL");
            $update->execute([
                "/etc/letsencrypt/live/{$matches[1]}/fullchain.pem",
                "/etc/letsencrypt/live/{$matches[1]}/privkey.pem",
                $id
            ]);
        } else {
            $this->info('Certificate error!');

            // Update domain
            $update = $this->pdo->prepare("UPDATE `domains` SET `ssl` = ? WHERE `id` = ? AND `ssl_certificate` IS NULL");
            $update->execute([-1, $id]);
        }
    }
}

$worker = new worker;
$worker->init();
