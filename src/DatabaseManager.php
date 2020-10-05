<?php


namespace Tkeer\Flattable;


use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;

class DatabaseManager
{
    /**
     * @var Builder
     */
    protected $db;

    const COL_NAME = 'column_name';
    const OP = 'op';
    const VALUE = 'value';

    public function __construct()
    {
        $this->db = \App::make('db');
    }

    public function insert($tableName, $data)
    {
        return $this->db->table($tableName)->insert($data);
    }

    public function update($tableName, $data, $wheres = [])
    {
        $query = $this->db->table($tableName);

        $this->applyConstraintsOnQuery($query, $wheres);

        $query->update($data);
    }

    public function delete($tableName, $wheres)
    {
        $query = $this->db->table($tableName);

        $this->applyConstraintsOnQuery($query, $wheres);

        $query->delete();
    }

    private function applyConstraintsOnQuery($query, $wheres)
    {
        if (is_callable($wheres)) {
            call_user_func($wheres, $query);

            return;
        }

        foreach ($wheres as $where) {
            $query->where(Arr::get($where, self::COL_NAME), Arr::get($where, self::OP, '='),
                Arr::get($where, self::VALUE));
        }
    }

    public function first($tableName, array $selects, array $wheres)
    {
        $query = $this->buildQueryForFetch($tableName, $selects, $wheres);

        return (array)$query->first();
    }

    private function buildQueryForFetch($tableName, array $selects, $wheres)
    {
        $query = $this->db->table($tableName);

        $query->select($selects);

        $this->applyConstraintsOnQuery($query, $wheres);

        return $query;
    }

    public function get($tableName, array $selects, $wheres)
    {
        $query = $this->buildQueryForFetch($tableName, $selects, $wheres);

        return $query->get()->toArray();
    }

    public function updateJson($flatTableName, $dataToFill, $flatTableJsonColName, $wheres)
    {
        $this->_updateJson($flatTableName, $dataToFill, $flatTableJsonColName, $wheres);
    }

    public function deleteJson($flatTableName, $toBeRemovedJsonModelId, $flatTableJsonColName, $wheres)
    {
        $this->_updateJson($flatTableName, ['id' => $toBeRemovedJsonModelId], $flatTableJsonColName, $wheres,
            $remove = true);
    }

    private function _updateJson($flatTableName, $dataToFill, $flatTableJsonColName, $wheres, $remove = false)
    {
        $flatTableRows = $this->getJsonDataFromTable($flatTableName, $flatTableJsonColName, $wheres);


        foreach ($flatTableRows as $flatTableRow) {
            $jsonData = $this->getJsonDataFromFlatTableRow($flatTableRow, $flatTableJsonColName);

            $newJsonData = collect($jsonData)->keyBy('id');

            $jsonId = data_get($dataToFill, 'id');

            //update or delete
            $remove ? $newJsonData->pull($jsonId) : $newJsonData->put($jsonId, $dataToFill);

            $newJsonData = $newJsonData->values()->toArray();

            $flatTableId = data_get($flatTableRow, 'id');

            $newJsonData = json_encode($newJsonData);

            $this->updateJsonCol($flatTableName, $flatTableId, $flatTableJsonColName, $newJsonData);
        }
    }

    /**
     * @param $tableName
     * @param $colName
     * @param $wheres
     * @return array
     */
    private function getJsonDataFromTable($tableName, $colName, $wheres)
    {
        $selects = [
            "$tableName.id",
            $colName,
        ];

        return $this->get($tableName, $selects, $wheres);
    }

    private function getJsonDataFromFlatTableRow($flatTableRow, $flatTableJsonColName)
    {
        $jsonCol = data_get($flatTableRow, $flatTableJsonColName);

        return json_decode($jsonCol, true);
    }

    private function updateJsonCol($flatTableName, $flatTableId, $jsonColName, $dataToUpdate)
    {
        $wheres[] = [
            self::COL_NAME => 'id',
            self::VALUE => $flatTableId,
        ];

        Arr::set($data, $jsonColName, $dataToUpdate);

        $this->update($flatTableName, $data, $wheres);
    }
}
