<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DynamicDatabaseExport implements FromQuery, WithHeadings, ShouldAutoSize, WithChunkReading
{
    protected array $exportData;
    protected bool $includeHeaders;

    public function __construct(array $exportData, bool $includeHeaders = true)
    {
        $this->exportData = $exportData;
        $this->includeHeaders = $includeHeaders;
    }

    public function query()
    {
        $table = $this->exportData['table'] ?? null;
        $columns = $this->exportData['columns'] ?? ['*'];
        $conditions = $this->exportData['conditions'] ?? [];
        $empresaId = $this->exportData['empresa_id'] ?? null;
        $sucursalId = $this->exportData['sucursal_id'] ?? null;
        $limit = $this->exportData['limit'] ?? null;
        $orderBy = $this->exportData['orderBy'] ?? null;
        $orderDirection = $this->exportData['orderDirection'] ?? 'asc';
        $dateColumn = $this->exportData['dateColumn'] ?? null;
        $dateFrom = $this->exportData['dateFrom'] ?? null;
        $dateTo = $this->exportData['dateTo'] ?? null;

        if (empty($table) || !Schema::hasTable($table)) {
            return DB::table('information_schema.tables')->whereRaw('1 = 0');
        }

        $validColumns = Schema::getColumnListing($table);
        $columns = array_filter($columns, fn($col) => in_array($col, $validColumns));

        if (empty($columns)) {
            return DB::table($table)->whereRaw('1 = 0');
        }

        $query = DB::table($table)->select($columns);

        if ($empresaId !== null && Schema::hasColumn($table, 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        if ($sucursalId !== null && Schema::hasColumn($table, 'sucursal_id')) {
            $query->where('sucursal_id', $sucursalId);
        }

        foreach ($conditions as $condition) {
            $col = $condition['column'] ?? null;
            $operator = strtoupper(trim($condition['operator'] ?? '='));
            $value = $condition['value'] ?? null;
            $logic = strtoupper(trim($condition['logic'] ?? 'AND'));

            if (empty($col) || !in_array($col, $validColumns)) {
                continue;
            }

            $method = $logic === 'OR' ? 'orWhere' : 'where';

            if ($operator === 'IS NULL') {
                $query->{$logic === 'OR' ? 'orWhereNull' : 'whereNull'}($col);
            } elseif ($operator === 'IS NOT NULL') {
                $query->{$logic === 'OR' ? 'orWhereNotNull' : 'whereNotNull'}($col);
            } elseif ($operator === 'LIKE') {
                $query->{$method}($col, 'LIKE', '%' . $value . '%');
            } elseif ($operator === 'IN') {
                $values = is_array($value) ? $value : array_map('trim', explode(',', $value));
                $query->{$logic === 'OR' ? 'orWhereIn' : 'whereIn'}($col, $values);
            } elseif ($operator === 'NOT IN') {
                $values = is_array($value) ? $value : array_map('trim', explode(',', $value));
                $query->{$logic === 'OR' ? 'orWhereNotIn' : 'whereNotIn'}($col, $values);
            } else {
                $query->{$method}($col, $operator, $value);
            }
        }

        if ($dateColumn && in_array($dateColumn, $validColumns)) {
            if ($dateFrom) {
                $query->where($dateColumn, '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where($dateColumn, '<=', $dateTo);
            }
        }

        if ($orderBy && in_array($orderBy, $validColumns)) {
            $query->orderBy($orderBy, in_array($orderDirection, ['asc', 'desc']) ? $orderDirection : 'asc');
        }

        if ($limit && is_numeric($limit) && $limit > 0) {
            $query->limit((int) $limit);
        }

        return $query;
    }

    public function headings(): array
    {
        if (!$this->includeHeaders) {
            return [];
        }

        $columns = $this->exportData['columns'] ?? [];

        return array_map(fn($col) => ucwords(str_replace('_', ' ', $col)), $columns);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
