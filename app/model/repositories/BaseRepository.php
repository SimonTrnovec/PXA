<?php

namespace App\Model\Repositories;

use App;
use Nette\Caching\Cache;
use  Dibi\Connection;
use Nette;
use Nette\NoImplemetedException;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

abstract class BaseRepository
{
    /** @var Connection Database connection resource */
    protected $db;

    /** @var  Cache */
    private $cache;

    /** @var  string Primary key name */
    protected $primaryKey = NULL;

    /** @var  string Table name */
    protected $table = NULL;

    /** @var  string Table alias */
    protected $alias = NULL;

    abstract protected function setup();

    public function __construct(Connection $db, Cache $c)
    {
        $this->db = $db;
        $this->cache = $c;

        $this->setup();

        Validators::assert($this->table, 'string','Table name');
        Validators::assert($this->primaryKey, 'string','Primary key');
        Validators::assert($this->alias, 'string','Alias');

    }

    public function startTransction()
    {
        $this->db->begin();
    }

    public function commitTransction()
    {
        $this->db->commit();
    }

    public function rollbackTransction()
    {
        $this->db->rollback();
    }


    /**
     * Deletes record from table
     *
     * @param int $id primary key
     */
    public function delete($id)
    {
        $this->db->delete($this->table)
            ->where('%n = %i', $this->primaryKey, $id)
            ->execute();
    }

    /**
     * Deletes all record from table
     *
     * @return \DibiFluent
     */
    public function deleteAll()
    {
        return $this->db->delete($this->table);
    }

    /**
     * Inserts new record into table
     *
     * @param array $values values to insert
     * @param boolean return generated id?
     *
     * @return int inserted row ID
     */
    public function insert($values)
    {
        $this->db->insert($this->table, $values)
            ->execute();

        return $this->db->insertId();
    }

    /**
     * Updates existing record
     *
     * @param int   $id     primary key of updated record
     * @param array $values array of updated values
     */
    public function update($id, $values)
    {
        $this->db->update($this->table, $values)
            ->where('%n = %i', $this->primaryKey, $id)
            ->execute();
    }

    /**
     * Updates all existing record
     *
     * @param array $values
     *
     * @return \DibiFluent
     */
    public function updateAll($values)
    {
        return $this->db->update($this->table, $values);
    }

    /**
     * Finds one record by primary key
     *
     * @param int $id primary key
     * @param boolean|string optional column name to search by
     *
     * @return \DibiRow|FALSE
     */
    public function find($id, $column = FALSE, $cache = FALSE)
    {
        $column = ($column === FALSE) ? $this->primaryKey : $column;

        $cacheKey = 'db:' . $this->table . ':' . $column . ':' . $id;
        $value = $cache !== FALSE ? $this->getCache($cacheKey) : NULL;
        if (is_null($value)) {
            $value = $this->db->select('*')
                ->from('%n', $this->table)
                ->where('%n = %s', $column, $id)
                ->fetch();

            if ($cache !== FALSE) {
                $this->setCache($cacheKey, $value, $cache);
            }
        }

        return $value;
    }

    public function getCount($languageId = NULL)
    {
        if ($this->translationTable === NULL) {
            return $this->db->select('COUNT(%n)', $this->alias . '.' . $this->primaryKey)
                ->from('%n %n', $this->table, $this->alias);
        } else {
            if ($languageId === NULL) {
                return $this->db->select('COUNT(DISTINCT %n)', $this->alias . '.' . $this->primaryKey)
                    ->from('%n %n', $this->table, $this->alias)
                    ->leftJoin('%n %n', $this->translationTable, $this->alias . '_tr')->on('(%n = %n)', $this->alias . '.' . $this->primaryKey, $this->alias . '_tr.' . $this->primaryKey)
                    ->leftJoin('[languages] l')->on('%n = [l.language_id] AND [l.is_default] = %i', $this->alias . '_tr.language_id', TRUE);
            } else {
                return $this->db->select('COUNT(DISTINCT %n)', $this->alias . '.' . $this->primaryKey)
                    ->from('%n %n', $this->table, $this->alias)
                    ->leftJoin('%n %n', $this->translationTable, $this->alias . '_tr')->on('(%n = %n AND %n = %i)', $this->alias . '.' . $this->primaryKey, $this->alias . '_tr.' . $this->primaryKey, $this->alias . '_tr.language_id', $languageId);
            }
        }
    }

    public function __call($name, $arguments)
    {
        // first we look if $name matches to exists or existsBy* function
        $matches = Strings::match($name, '^existsBy(.*)^');

        if ($name == 'exists' || (is_countable($matches) && count($matches) == 2)) {
            if ($name == 'exists') {
                $column = $this->primaryKey;
                $value = $arguments[0];
                $invert = FALSE;
                $exclude = FALSE;
            } else {
                $column = Strings::lower($matches[1]);
                if (count($arguments) == 2) {
                    [$control, $extra] = $arguments;
                    if ($control instanceof \Nette\Forms\IControl) {
                        $value = $control->value;
                        $invert = TRUE;
                    } else {
                        $value = $control;
                        $invert = FALSE;
                    }
                } else {
                    $value = $arguments[0];
                    $extra = NULL;
                    $invert = FALSE;
                }
                $exclude = ($extra === NULL) ? FALSE : $extra;
            }

            if ($value == '') { // dont check for existence of empty values
                return TRUE;
            }

            $query = $this->db->select('COUNT(*)')
                ->from('%n', $this->table)
                ->where('%n = %s', $column, $value);

            if ($exclude) {
                if (isset($exclude['invert'])) {
                    $invert = FALSE;
                } else {
                    $query->where('%n != %s', $this->primaryKey, $exclude);
                }
            }

            $result = (bool) $query->fetchSingle();

            return ($invert) ? !$result : $result;
        }

        // otherwise we check if it is findOneBy*
        $matches = Strings::match($name, '^findOneBy(.*)^');
        if (is_countable($matches) && count($matches) == 2) {
            $column = Strings::lower($matches[1]);

            return $this->find($arguments[0], $column);
        }

        // else we check if it is findAllBy*
        $matches = Strings::match($name, '^findAllBy(.*)^');
        if (is_countable($matches) && count($matches) == 2) {
            $column = Strings::lower($matches[1]);

            return $this->db->select('*')
                ->from($this->table)
                ->where('%n = %s', $column, $arguments[0]);
        }

        throw new NotImplementedException("Method '" . get_called_class() . "::$name' does not exist!");
    }

    public function findPlain()
    {
        return $this->db->select('%n.*', $this->alias)
            ->from('%n %n', $this->table, $this->alias);
    }

    public function findAll($languageId = NULL, $appendSelect = '')
    {
        if ($appendSelect != '') {
            $appendSelect = ', ' . $appendSelect;
        }

        if ($this->translationTable === NULL) {
            return $this->db->select('%n.*' . $appendSelect, $this->alias)
                ->from('%n %n', $this->table, $this->alias);
        } else {
            if ($languageId === NULL) {
                return $this->db->select('%n.*, %n.*' . $appendSelect, $this->alias . '_tr', $this->alias)
                    ->from('%n %n', $this->table, $this->alias)
                    ->leftJoin('%n %n', $this->translationTable, $this->alias . '_tr')->on('(%n = %n)', $this->alias . '.' . $this->primaryKey, $this->alias . '_tr.' . $this->primaryKey)
                    ->leftJoin('[languages] l')->on('%n = [l.language_id] AND [l.is_default] = %i', $this->alias . '_tr.language_id', TRUE)
                    ->groupBy('%n', $this->alias . '.' . $this->primaryKey);
            } else {
                return $this->db->select('%n.*, %n.*' . $appendSelect, $this->alias . '_tr', $this->alias)
                    ->from('%n %n', $this->table, $this->alias)
                    ->leftJoin('%n %n', $this->translationTable, $this->alias . '_tr')->on('(%n = %n AND %n = %i)', $this->alias . '.' . $this->primaryKey, $this->alias . '_tr.' . $this->primaryKey, $this->alias . '_tr.language_id', $languageId)
                    ->groupBy('%n', $this->alias . '.' . $this->primaryKey);
            }
        }
    }

    public function findTranslations()
    {
        return $this->db->select('*')
            ->from('%n %n', $this->translationTable, $this->alias . '_tr');
    }

    public function softDelete($id)
    {
        $this->update($id, [
            'state' => App\Model\Enums\VisibilityStatesEnum::DELETED,
        ]);
    }

    /**
     * Returns associative array slug => slug into routing table
     *
     * @param string $column
     *
     * @return array
     */
    public function getFilterTable($column = 'slug')
    {
        return $this->db->select('%n as [col1], %n as [col2]', $column, $column)
            ->from($this->translationTable)
            ->fetchPairs();
    }

    public function getDb()
    {
        return $this->db;
    }


    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getCache($key)
    {
        return $this->cache->load($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $expireSeconds
     */
    public function setCache($key, $value, $expireSeconds = 600)
    {
        $this->cache->save($key, $value, [
            Nette\Caching\Cache::EXPIRE => $expireSeconds,
        ]);
    }

    /**
     * @param string $key
     */
    public function removeCache($key)
    {
        $this->cache->remove($key);
    }


}