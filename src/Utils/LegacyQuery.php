<?php

namespace App\Utils;

use Doctrine\ORM\EntityManager;
use ErrorException;
use PDO;
use PDOStatement;

class LegacyQuery {
    /** @var false|PDOStatement */
    private $statement;
    /** @var PDO|null */
    private $connection;
    /** @var bool */
    private $success = false;
    
    public function __construct(string $sql, EntityManager $em)
    {
        $connection = $em->getConnection();
        $this->connection = $connection->getNativeConnection();
        $this->statement = $this->connection->prepare($sql);
    }
    
    public function bind(string $key, $value, int $type = PDO::PARAM_STR): self
    {
        $this->statement->bindValue($key, $value, $type);
        
        return $this;
    }
    
    /**
     * @throws \ErrorException|\Doctrine\DBAL\Exception
     */
    public function execute(bool $force = true): self
    {
        $this->success = $this->statement->execute();
        
        if ($force && !$this->success)
            throw new ErrorException($this->getStatement()->errorInfo());
        
        return $this;
    }
    
    public function isSuccessful(): bool
    {
        return $this->success;
    }
    
    public function fetch(): array
    {
        return $this->statement->fetch() ?: [];
    }
    
    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }
    
    public function didFetch(): bool
    {
        return $this->rowCount() > 0;
    }
    
    public function fetchAll(): array
    {
        return $this->statement->fetchAll();
    }
    
    public function getStatement(): PDOStatement
    {
        return $this->statement;
    }
    
    public function fetchOne()
    {
        return $this->statement->fetchColumn();
    }
}