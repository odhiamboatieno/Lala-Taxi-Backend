<?php

namespace Yajra\DataTables\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use ReflectionMethod;
use Yajra\DataTables\Html\Builder;

abstract class DataTable
{
    protected ?Builder $htmlBuilder = null;

    abstract public function dataTable($query);

    public function ajax(): JsonResponse
    {
        return $this->dataTable($this->getQuery())->make(true);
    }

    public function render($view, array $data = [], array $mergeData = [])
    {
        if (request()->ajax()) {
            return $this->ajax();
        }

        $data['dataTable'] = $this->html();

        return view($view, $data, $mergeData);
    }

    public function builder(): Builder
    {
        return $this->htmlBuilder ??= app(Builder::class);
    }

    protected function getQuery()
    {
        $method = new ReflectionMethod($this, 'query');
        $parameter = $method->getParameters()[0] ?? null;
        $type = $parameter?->getType();

        if ($type && ! $type->isBuiltin()) {
            $class = $type->getName();

            return $this->query(app($class));
        }

        return $this->query();
    }

    protected function filename(): string
    {
        return Str::snake(class_basename(static::class)).'_'.date('YmdHis');
    }
}
