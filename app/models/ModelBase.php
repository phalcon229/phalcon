<?php

class ModelBase extends Phalcon\Di\Injectable
{
    protected $db;
    protected $table;

    private $stmt;
    private $fetchAll = 'fetchAll';
    private $fetchRow = 'fetch';
    private $fetchOne = 'fetchColumn';

    protected $sql;

    public function __construct()
    {
        $this->db = $this->di['db'];
    }

    public function query($sql, $params = [])
    {
        $this->sql = $sql;
        $this->stmt = $this->db->prepare($this->sql);

        !$params or $this->bindValue($params);

        if($this->stmt->execute())
            $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
        else
            throw new ModelException($this->stmt->errorInfo());
    }

    private function bindValue($params)
    {
        foreach($params as $k => $v)
        {
            if(is_int($k))
                $k += 1;

            switch(TRUE)
            {
                case is_int($v):
                    $type = \PDO::PARAM_INT;
                    break;
                case is_bool($v):
                    $type = \PDO::PARAM_BOOL;
                    break;
                case is_null($v):
                    $type = \PDO::PARAM_NULL;
                    break;
                default:
                    $type = \PDO::PARAM_STR;
            }

            $this->stmt->bindValue($k, $v, $type);
        }
    }

    public function getAll()
    {
        return $this->fetch($this->fetchAll);
    }

    public function getRow()
    {
        return $this->fetch($this->fetchRow);
    }

    public function getOne()
    {
        return $this->fetch($this->fetchOne);
    }

    private function fetch($func)
    {
        $rs = $this->stmt->$func();
        unset($this->stmt);
        return $rs;
    }

    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function affectRow()
    {
        return $this->stmt->rowCount();
    }

    protected function resourceCount()
    {
        return $this->stmt->columnCount();
    }

    protected function generatePhForIn($n)
    {
        return rtrim(str_repeat('?,', $n), ',');
    }

    protected function generatePhForSet($params)
    {
        return ' SET ' . implode(',', array_map(function($v) { return $v . ' = ?'; }, $params));
    }

    public final function insert(array $insert)
    {
        $sets = $this->generatePhForSet(array_keys($insert));
        $this->query('INSERT INTO ' . $this->table . $sets, array_values($insert));
        return $this->lastInsertId();
    }

    public final function update(array $update, $where = [])
    {
        $sets = $this->generatePhForSet(array_keys($update));
        $values = array_values($update);

        $sql = 'UPDATE ' . $this->table . $sets;
        if($where)
        {
            $sql .= ' WHERE ' . $where['condition'];
            $values = array_merge($values, array_values($where['values']));
        }

        $this->query($sql, $values);
        return $this->affectRow();
    }

    public final function delete($where = [])
    {
        $sql = 'DELETE FROM ' . $this->table;
        $values = [];
        if($where)
        {
            $sql .= ' WHERE ' . $where['condition'];
            !empty($where['values']) ? $values = array_merge($values, array_values($where['values'])) : '';
        }

        $this->query($sql, $values);
        return $this->affectRow();
    }

    protected final function begin()
    {
        $this->db->begin();
    }

    protected final function commit()
    {
        $this->db->commit();
    }

    protected final function rollback()
    {
        $this->db->rollback();
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function getTable()
    {
        return $this->table;
    }
}
