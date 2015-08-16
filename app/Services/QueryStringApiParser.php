<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class QueryStringAPiParser {

    const SORT_IDENTIFIER = 'sort';
    const SORT_DIRECTION_ASC = 'asc';
    const SORT_DIRECTION_DESC = 'desc';
    const SORT_DELIMITER = ',';
    const SORT_DESC_IDENTIFIER = '-';

    const FILTER_DELIMITER = ',';

    /**
     * @var Request
     */
    protected $request;

    protected $model;

    /**
     * @var array
     */
    protected $columnNames;

    protected $queryBuilder;

    /**
     * @param Request $request
     * @param $model
     */
    public function __construct(Request $request, $model)
    {
        $this->request = $request;
        $this->model = $model;
        $this->queryBuilder = DB::table($model->getTable());
        $this->setColumnsNames();
    }


    /**
     * @return mixed
     * @throws \Exception
     */
    public function parser() {
        $data = $this->request->except('page');

        foreach ($data as $field => $value) {
            $field = trim($field);
            if ($field == self::SORT_IDENTIFIER) {
                $this->addSort($value);
            } else {
                $this->addFilter($field, $value);
            }
        }

        return $this->queryBuilder;
    }

    /**
     * @param $field
     * @param $value
     * @throws \Exception
     */
    private function addFilter($field, $value)
    {
        if(array_search($field, $this->columnNames) === false) {
            throw new \Exception("Invalid query! Field {$field} not mapped");
        }

        $values = explode(self::FILTER_DELIMITER, $value);

        $this->queryBuilder->where(function ($query) use ($values, $field) {
            foreach ($values as $whereValue) {
                $quoteValue = DB::connection()->getPdo()->quote($whereValue);
                $query->orWhere($field, $quoteValue);
            }
        });
    }

    /**
     * @param $value
     * @throws \Exception
     */
    private function addSort($value)
    {
        $fields = explode(self::SORT_DELIMITER, $value);

        foreach ($fields as $field) {

            $direction = self::SORT_DIRECTION_ASC;

            if(substr($field, 0, 1) == self::SORT_DESC_IDENTIFIER) {
                $direction = self::SORT_DIRECTION_DESC;
                $field = str_replace(self::SORT_DESC_IDENTIFIER, '', $field);
            }

            if(array_search($field, $this->columnNames) === false) {
                throw new \Exception("Invalid query! Field {$field} not mapped");
            }

            $this->queryBuilder->orderBy($field, $direction);
        }
    }

    private function setColumnsNames()
    {
        $connection = DB::connection();
        $this->columnNames = $connection->getSchemaBuilder()->getColumnListing($this->model->getTable());
    }

}