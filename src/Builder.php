<?php
namespace Exeko\QueryFilter;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class Builder extends \Illuminate\Database\Eloquent\Builder
{
  protected $relationConstraints = [];

  public function filter($filter) {
    foreach ($filter as $input_name => $value) {
      $this->applyFilter($input_name, $value);
    }
    return $this;
  }

  public function applyFilter($input_name, $value) {
    if ($value == '') {
      return; // If value is empty, going to next iteration
    }

    //Get the column_name and operator
    $col_name_operator = explode(':', $input_name);
    $column = $col_name_operator[0];

    if (count($col_name_operator) > 1) {
      $operator = $col_name_operator[1];
      if ($column == '') {
        //call method
        $this->model->{$operator}($value, $this);
      } else {
        $operator = $this->checkOperator($this, $operator);

        if (!$this->isRelationProperty($column)) {
          //default
          $value = $this->checkValue($this, $column, $operator, $value);
          $this->where($column, $operator, $value);
        } else {
          //relation
          $this->withRelationConstraint($value, $column, $operator);
        }
      }
    } else { // no operator provided: using =
      if (!$this->isRelationProperty($column)) {
        //default
        $this->where($column, '=', $value);
      } else {
        //relation
        $this->withRelationConstraint($value, $column, '=');
      }
    }
  }

  protected function isRelationProperty(string $property) : bool {
      if (! Str::contains($property, '.')) {
        return false;
      }

      if (in_array($property, $this->relationConstraints)) {
        return false;
      }

      if (Str::startsWith($property, $this->getModel()->getTable().'.')) {
        return false;
      }

      return true;
  }

  protected function withRelationConstraint($value, string $property, $operator) {
      [$relation, $property] = collect(explode('.', $property))
        ->pipe(function (Collection $parts) {
          return [
            $parts->except(count($parts) - 1)->map([Str::class, 'camel'])->implode('.'),
            $parts->last(),
          ];
        });

      $operator = $this->checkOperator($this, $operator);
      $this->whereHas($relation, function ($query) use ($property, $operator, $value) {
          $value = $this->checkValue($query, $property, $operator, $value);
          $query->where($property, $operator, $value);
      });
  }

  protected function checkOperator($builder, $operator) {
    if (in_array($operator, $builder->getQuery()->operators)) {
      return $operator;
    } else {
      throw new \Exception('Undefined operator ' . $operator);
    }
  }
  protected function checkValue($builder, $column, $operator, $value) {
    $model = $builder->getModel();
    if (method_exists($model, 'set' . $column . 'attribute')) {
      $model->{'set' . $column . 'attribute'}($value);
      $value = $model->getAttributes()[$column];
    }
    if ($operator == 'like' || $operator == 'ilike') {
      $value = '%' . $value . '%';
    }
    return $value;
  }
}
