<?php

declare (strict_types=1);
namespace Prettus\Repository\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Prettus\Repository\Contracts\Criteria_Interface;
use Prettus\Repository\Contracts\Repository_Interface;
/**
 * Class RequestCriteria
 * @package Prettus\Repository\Criteria
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Request_Criteria implements Criteria_Interface
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Apply criteria in query repository
     *
     * @param         Builder|Model     $model
     *
     * @return mixed
     * @throws \Exception
     */
    public function apply($model, Repository_Interface $repository)
    {
        $fields_searchable = $repository->get_fields_searchable();
        $search = $this->request->get(config('repository.criteria.params.search', 'search'), null);
        $search_fields = $this->request->get(config('repository.criteria.params.searchFields', 'searchFields'), null);
        $filter = $this->request->get(config('repository.criteria.params.filter', 'filter'), null);
        $order_by = $this->request->get(config('repository.criteria.params.orderBy', 'orderBy'), null);
        $sorted_by = $this->request->get(config('repository.criteria.params.sortedBy', 'sortedBy'), 'asc');
        $with = $this->request->get(config('repository.criteria.params.with', 'with'), null);
        $with_count = $this->request->get(config('repository.criteria.params.withCount', 'withCount'), null);
        $search_join = $this->request->get(config('repository.criteria.params.searchJoin', 'searchJoin'), null);
        $sorted_by = !empty($sorted_by) ? $sorted_by : 'asc';
        // Restrict sort direction to safe values to prevent SQL injection via the direction token.
        $sorted_by = in_array(strtolower($sorted_by), ['asc', 'desc'], true) ? strtolower($sorted_by) : 'asc';
        if ($search && is_array($fields_searchable) && count($fields_searchable)) {
            $search_fields = is_array($search_fields) || is_null($search_fields) ? $search_fields : explode(';', $search_fields);
            $is_first_field = true;
            $search_data = $this->parser_search_data($search);
            $fields = $this->parser_fields_search($fields_searchable, $search_fields, array_keys($search_data));
            $search = $this->parser_search_value($search);
            $model_force_and_where = strtolower($search_join) === 'and';
            $model = $model->where(function ($query) use ($fields, $search, $search_data, $is_first_field, $model_force_and_where): void {
                /** @var Builder $query */
                foreach ($fields as $field => $condition) {
                    if (is_numeric($field)) {
                        $field = $condition;
                        $condition = '=';
                    }
                    $value = null;
                    $condition = trim(strtolower($condition));
                    if (isset($search_data[$field])) {
                        $value = $condition == 'like' || $condition == 'ilike' ? "%{$search_data[$field]}%" : $search_data[$field];
                    } else if (!is_null($search) && !in_array($condition, ['in', 'between'])) {
                        $value = $condition == 'like' || $condition == 'ilike' ? "%{$search}%" : $search;
                    }
                    $relation = null;
                    if (stripos($field, '.')) {
                        $explode = explode('.', $field);
                        $field = array_pop($explode);
                        $relation = implode('.', $explode);
                    }
                    if ($condition === 'in') {
                        $value = explode(',', $value);
                        if (trim($value[0]) === '' || $field == $value[0]) {
                            $value = null;
                        }
                    }
                    if ($condition === 'between') {
                        $value = explode(',', $value);
                        if (count($value) < 2) {
                            $value = null;
                        }
                    }
                    $model_table_name = $query->get_model()->get_table();
                    if ($is_first_field || $model_force_and_where) {
                        if (!is_null($value)) {
                            if (!is_null($relation)) {
                                $query->where_has($relation, function ($query) use ($field, $condition, $value): void {
                                    if ($condition === 'in') {
                                        $query->where_in($field, $value);
                                    } elseif ($condition === 'between') {
                                        $query->where_between($field, $value);
                                    } else {
                                        $query->where($field, $condition, $value);
                                    }
                                });
                            } else if ($condition === 'in') {
                                $query->where_in($model_table_name . '.' . $field, $value);
                            } elseif ($condition === 'between') {
                                $query->where_between($model_table_name . '.' . $field, $value);
                            } else {
                                $query->where($model_table_name . '.' . $field, $condition, $value);
                            }
                            $is_first_field = false;
                        }
                    } else if (!is_null($value)) {
                        if (!is_null($relation)) {
                            $query->or_where_has($relation, function ($query) use ($field, $condition, $value): void {
                                if ($condition === 'in') {
                                    $query->where_in($field, $value);
                                } elseif ($condition === 'between') {
                                    $query->where_between($field, $value);
                                } else {
                                    $query->where($field, $condition, $value);
                                }
                            });
                        } else if ($condition === 'in') {
                            $query->or_where_in($model_table_name . '.' . $field, $value);
                        } elseif ($condition === 'between') {
                            $query->where_between($model_table_name . '.' . $field, $value);
                        } else {
                            $query->or_where($model_table_name . '.' . $field, $condition, $value);
                        }
                    }
                }
            });
        }
        if (isset($order_by) && !empty($order_by)) {
            $order_by_split = explode(';', $order_by);
            if (count($order_by_split) > 1) {
                $sorted_by_split = explode(';', $sorted_by);
                foreach ($order_by_split as $order_by_split_item_key => $order_by_split_item) {
                    $sorted_by = $sorted_by_split[$order_by_split_item_key] ?? $sorted_by_split[0];
                    $model = $this->parser_fields_order_by($model, $order_by_split_item, $sorted_by);
                }
            } else {
                $model = $this->parser_fields_order_by($model, $order_by_split[0], $sorted_by);
            }
        }
        if (isset($filter) && !empty($filter)) {
            if (is_string($filter)) {
                $filter = explode(';', $filter);
            }
            $model = $model->select($filter);
        }
        if ($with) {
            $with = explode(';', $with);
            $model = $model->with($with);
        }
        if ($with_count) {
            $with_count = explode(';', $with_count);
            $model = $model->with_count($with_count);
        }
        return $model;
    }
    /**
     * Validate that a table or column identifier contains only safe characters.
     *
     * Allows alphanumeric characters, underscores, hyphens, and dots (for
     * table.column notation). Rejects any input that could be used to inject
     * raw SQL tokens into a leftJoin() or orderBy() call.
     *
     * @param  string  $identifier
     * @return bool
     */
    protected function is_valid_identifier(string $identifier): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_.\-]+$/', $identifier);
    }
    /**
     * @param $model
     * @param $orderBy
     * @param $sortedBy
     * @return mixed
     */
    protected function parser_fields_order_by($model, $order_by, $sorted_by)
    {
        // Sanitize sort direction to asc/desc only.
        $sorted_by = in_array(strtolower((string) $sorted_by), ['asc', 'desc'], true) ? strtolower($sorted_by) : 'asc';
        $split = explode('|', $order_by);
        if (count($split) > 1) {
            /*
             * ex.
             * products|description -> join products on current_table.product_id = products.id order by description
             *
             * products:custom_id|products.description -> join products on current_table.custom_id = products.id order
             * by products.description (in case both tables have same column name)
             */
            $table = $model->get_model()->get_table();
            $sort_table = $split[0];
            $sort_column = $split[1];
            $split = explode(':', $sort_table);
            $local_key = '.id';
            if (count($split) > 1) {
                $sort_table = $split[0];
                $comma_exp = explode(',', $split[1]);
                $key_name = $table . '.' . $split[1];
                if (count($comma_exp) > 1) {
                    $key_name = $table . '.' . $comma_exp[0];
                    $local_key = '.' . $comma_exp[1];
                }
            } else {
                /*
                 * If you do not define which column to use as a joining column on current table, it will
                 * use a singular of a join table appended with _id
                 *
                 * ex.
                 * products -> product_id
                 */
                $prefix = Str::singular($sort_table);
                $key_name = $table . '.' . $prefix . '_id';
            }
            // Validate table and column identifiers before interpolating them into SQL.
            // Laravel's leftJoin() and orderBy() do not quote bare string arguments, so
            // an attacker-supplied value like "evil_table; DROP TABLE users--" would be
            // executed verbatim. Allow only safe identifier characters.
            if (!$this->is_valid_identifier($sort_table) || !$this->is_valid_identifier($sort_column)) {
                return $model;
            }
            return $model->left_join($sort_table, $key_name, '=', $sort_table . $local_key)->order_by($sort_column, $sorted_by)->add_select($table . '.*');
        }
        // Validate the plain orderBy column name as well.
        if (!$this->is_valid_identifier($order_by)) {
            return $model;
        }
        return $model->order_by($order_by, $sorted_by);
    }
    /**
     * @param $search
     */
    protected function parser_search_data($search): array
    {
        $search_data = [];
        if (stripos($search, ':')) {
            $fields = explode(';', $search);
            foreach ($fields as $row) {
                try {
                    [$field, $value] = explode(':', $row);
                    $search_data[$field] = $value;
                } catch (\Exception $e) {
                    //Surround offset error
                }
            }
        }
        return $search_data;
    }
    /**
     * @param $search
     */
    protected function parser_search_value($search)
    {
        if (stripos($search, ';') || stripos($search, ':')) {
            $values = explode(';', $search);
            foreach ($values as $value) {
                $s = explode(':', $value);
                if (count($s) == 1) {
                    return $s[0];
                }
            }
            return null;
        }
        return $search;
    }
    protected function parser_fields_search(array $fields = [], ?array $search_fields = null, ?array $data_keys = null)
    {
        if (!is_null($search_fields) && count($search_fields)) {
            $accepted_conditions = config('repository.criteria.acceptedConditions', ['=', 'like']);
            $original_fields = $fields;
            $fields = [];
            foreach ($search_fields as $index => $field) {
                $field_parts = explode(':', $field);
                $temporary_index = array_search($field_parts[0], $original_fields);
                if (count($field_parts) == 2) {
                    if (in_array($field_parts[1], $accepted_conditions)) {
                        unset($original_fields[$temporary_index]);
                        $field = $field_parts[0];
                        $condition = $field_parts[1];
                        $original_fields[$field] = $condition;
                        $search_fields[$index] = $field;
                    }
                }
            }
            if (!is_null($data_keys) && count($data_keys)) {
                $search_fields = array_unique(array_merge($data_keys, $search_fields));
            }
            foreach ($original_fields as $field => $condition) {
                if (is_numeric($field)) {
                    $field = $condition;
                    $condition = '=';
                }
                if (in_array($field, $search_fields)) {
                    $fields[$field] = $condition;
                }
            }
            if (count($fields) == 0) {
                throw new \Exception(trans('repository::criteria.fields_not_accepted', ['field' => implode(',', $search_fields)]));
            }
        }
        return $fields;
    }
}