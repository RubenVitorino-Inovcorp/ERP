<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Trait que centraliza a lógica de pesquisa, ordenação e paginação server-side
 * para uso nos controladores com o componente DataTable do frontend.
 */
trait Searchable
{
    /**
     * Aplica pesquisa, ordenação e paginação ao query builder.
     *
     * @param  Builder  $query  Query base (ex: Entity::query())
     * @param  Request  $request  Request HTTP com os parâmetros search, sort, direction, per_page
     * @param  array<string>  $searchableFields  Campos normais pesquisáveis via LIKE (ex: ['name', 'website'])
     * @param  array<string, string>  $blindIndexFields  Campos cifrados pesquisáveis via whereBlind (ex: ['nif' => 'nif_index'])
     * @param  array<string>  $sortableFields  Colunas permitidas para ordenação (ex: ['name', 'created_at'])
     * @param  string  $defaultSort  Coluna de ordenação por defeito
     * @param  string  $defaultDirection  Direção por defeito (asc|desc)
     */
    protected function applyDataTable(
        Builder $query,
        Request $request,
        array $searchableFields = [],
        array $blindIndexFields = [],
        array $sortableFields = [],
        string $defaultSort = 'id',
        string $defaultDirection = 'desc',
    ): LengthAwarePaginator {
        $search = $request->input('search');
        $sort = $request->input('sort', $defaultSort);
        $direction = $request->input('direction', $defaultDirection);
        $perPage = min((int) $request->input('per_page', 15), 50);

        // Pesquisa: campos normais (LIKE) e campos cifrados (Blind Index)
        if ($search) {
            $query->where(function (Builder $q) use ($search, $searchableFields, $blindIndexFields) {
                foreach ($searchableFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }

                foreach ($blindIndexFields as $field => $indexName) {
                    $model = $q->getModel();
                    $q->orWhereBlind($field, $indexName, $search);
                }
            });
        }

        // Ordenação (apenas por campos permitidos)
        if (in_array($sort, $sortableFields, true)) {
            $query->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy($defaultSort, $defaultDirection);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Retorna os filtros atuais para o componente Vue DataTable.
     */
    protected function getDataTableFilters(Request $request): array
    {
        return [
            'search' => $request->input('search', ''),
            'sort' => $request->input('sort', ''),
            'direction' => $request->input('direction', 'asc'),
            'per_page' => (int) $request->input('per_page', 15),
        ];
    }
}
