<?php

/**
 * PDOHelper Class
 *
 * @author Sander Tuinstra <sandert2001@hotmail.com>
 * @link https://github.com/SanderT2001/PDOHelper.git
 */
namespace PDOHelper;

require_once 'autoload.php';

use pdohelper\MSSQB;
use pdohelper\PDOConnection;

class PDOHelper
{
    /**
     * @var PDOConnection
     */
    protected $pdo = null;

    /**
     * @var SqlQueryBuilder
     */
    protected $queryBuilder = null;

    /**
     * @var string
     */
    protected $table = null;

    /**
     * @var string
     */
    protected $entityPath = null;

    /**
     * @var bool
     */
    protected $debug = false;

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

    public function getTable(): ?string
    {
        return $this->table;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        $this->pdo->setTable($table);
        $this->queryBuilder->setTable($table);

        return $this;
    }

    public function getEntityPath(): ?string
    {
        return $this->entityPath;
    }

    public function setEntityPath(string $path): self
    {
        $this->entityPath = $path;
        $this->pdo->setEntityPath($path);
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
