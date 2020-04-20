<?php

/**
 * PDOHelper Class
 *
 * @author Sander Tuinstra <sandert2001@hotmail.com>
 * @link https://github.com/SanderT2001/PDOHelper.git
 */
namespace PDOHelper;

include_once './PDOHelper/autoload.php';

use PDOHelper\MSSQB;
use PDOHelper\PDOConnection;

class PDOHelper
{
    /**
     * @var PDOConnection
     */
    private $pdo = null;

    /**
     * @var SqlQueryBuilder
     */
    private $queryBuilder = null;

    /**
     * @var string
     */
    private $table = null;

    /**
     * @var bool
     */
    private $debug = false;

    public function __construct(
        string $host,
        string $database,
        string $username,
        string $password,
        string $table_prefix = null
    ) {
        $this->connection = $this->setConnection(
            $host,
            $database,
            $username,
            $password,
            $table_prefix
        );

        $this->queryBuilder = new MSSQB();
    }

    public function getBuilder(): ?MSSQB
    {
        return $this->queryBuilder;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        $this->pdo->setTable($table);
        $this->queryBuilder->setTable($table);

        return $this;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        $this->pdo->setDebug($debug);
        return $this;
    }

    /**
     * @return array When using fetchAll()
     *         bool  When using execute()
     */
    public function execute(string $query)
    {
        return (stripos($query, 'SELECT') !== false) ? $this->pdo->fetchAll($query) : $this->pdo->execute($query);
    }

    private function setConnection(
        string $host,
        string $database,
        string $username,
        string $password,
        string $table_prefix = null
    ): self {
        $this->pdo = new PDOConnection();
        $this->pdo->setConfig(
            $host,
            $database,
            $username,
            $password,
            $table_prefix
        );
        return $this;
    }
}
