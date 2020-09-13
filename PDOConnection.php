<?php

namespace PDOHelper;

/**
 * PDOConnection Class
 *
 * @author Sander Tuinstra <sandert2001@hotmail.com>
 * @link https://github.com/SanderT2001/PDOHelper.git
 */
class PDOConnection
{
    // Data Source Name
    private const PDO_DSN = 'mysql:host=$host;dbname=$db';

    private $pdo          = null;

    private $host         = 'localhost';

    private $username     = 'root';

    private $password     = 'root';

    private $database     = null;

    private $tablePrefix  = null;

    private $table        = null;

    private $entity_path  = null;

    private $debug        = false;

    public function __construct() { }

    private function getPDO(): \PDO
    {
        return $this->pdo;
    }

    private function setPDO(\PDO $pdo): self
    {
        $this->pdo = $pdo;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    private function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getDatabase(): ?string
    {
        return $this->database;
    }

    public function setDatabase(string $database): self
    {
        $this->database = $database;
        return $this;
    }

    public function getTablePrefix(): ?string
    {
        return $this->tablePrefix;
    }

    public function setTablePrefix(string $prefix): self
    {
        $this->tablePrefix = $prefix;
        return $this;
    }

    public function getTable(): ?string
    {
        return $this->table;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function getEntityPath(): ?string
    {
        return $this->entity_path;
    }

    public function setEntityPath($path): self
    {
        $this->entity_path = $path;
        return $this;
    }

    public function getDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        if ($debug === true)
            $this->getPDO()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $this;
    }

    // wrapper functie
    public function setConfig(
        $host,
        $database,
        $username,
        $password,
        $tablePrefix = null
    ): \PDO {
        $this->setHost($host)
             ->setUsername($username)
             ->setPassword($password)
             ->setDatabase($database);
        if ($tablePrefix !== null) {
            $this->setTablePrefix($tablePrefix);
        }

        // Create a new PDO Instance here, because if the database changes for example, with a new setConfig() call, then the
        //   current Instance of PDO will be incorrect.
        $dsn = strtr($this::PDO_DSN, [
            '$host' => $this->getHost(),
            '$db'   => $this->getDatabase()
        ]);
        $this->setPDO(new \PDO($dsn, $this->getUsername(), $this->getPassword()));
        return $this->getPDO();
    }

    public function fetchAll(string $query): array
    {
        if ($this->getEntityPath() === null) {
            return $this->execute($query, true)->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $this->execute($query, true)->fetchAll(\PDO::FETCH_CLASS, $this->getEntityPath());
    }

    public function execute(string $query, bool $returnHandler = false)
    {
        $queryHandler = $this->getPDO();

        $handler = $queryHandler->prepare($query);
        $result  = $handler->execute();
        return ($returnHandler) ? $handler : $result;
    }
}
