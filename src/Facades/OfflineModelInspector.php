<?php

declare(strict_types=1);

namespace Victormgomes\QueryParams\Facades;

use Illuminate\Database\Eloquent\ModelInspector;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class OfflineModelInspector extends ModelInspector
{
    /**
     * Obtém atributos baseando-se apenas em código:
     * PK + Timestamps + SoftDeletes + Fillable + Casts + Foreign Keys dos Relacionamentos.
     */
    protected function getAttributes($model)
    {
        // 1. Inicia coleção de atributos conhecidos
        $attributes = collect();

        // 2. Adiciona Primary Key
        $attributes->push([
            'name' => $model->getKeyName(),
            'type' => $model->getKeyType(),
            'increments' => $model->getIncrementing(),
            'nullable' => false,
            'default' => null,
            'unique' => true,
            'fillable' => false,
            'hidden' => $this->attributeIsHidden($model->getKeyName(), $model),
            'appended' => null,
            'cast' => $this->getCastType($model->getKeyName(), $model),
        ]);

        // 3. Adiciona Timestamps (created_at, updated_at)
        if ($model->usesTimestamps()) {
            foreach ([$model->getCreatedAtColumn(), $model->getUpdatedAtColumn()] as $col) {
                if ($col) {
                    $attributes->push($this->makeArtificialColumn($col, $model, 'datetime'));
                }
            }
        }

        // 4. Adiciona Soft Deletes (deleted_at)
        if (method_exists($model, 'getDeletedAtColumn')) {
            $attributes->push($this->makeArtificialColumn($model->getDeletedAtColumn(), $model, 'datetime'));
        }

        // 5. Adiciona atributos do $fillable
        foreach ($model->getFillable() as $name) {
            $attributes->push($this->makeArtificialColumn($name, $model));
        }

        // 6. Adiciona atributos do $casts (que não estejam no fillable)
        foreach (array_keys($model->getCasts()) as $name) {
            $attributes->push($this->makeArtificialColumn($name, $model));
        }

        // 7. Inteligência: Extrai Foreign Keys de relacionamentos BelongsTo/MorphTo
        // Se o model tem "public function user() { return $this->belongsTo(...); }"
        // Então sabemos que existe uma coluna "user_id" (ou a que estiver definida).
        $relations = $this->getRelations($model);

        // Precisamos instanciar as relações para pegar as chaves
        foreach ($relations as $relationMeta) {
            if (! method_exists($model, $relationMeta['name'])) {
                continue;
            }

            try {
                // Invoca o método da relação (ex: $model->user())
                $relationObj = $model->{$relationMeta['name']}();

                if ($relationObj instanceof BelongsTo) {
                    // Adiciona a FK (ex: user_id)
                    $fkName = $relationObj->getForeignKeyName();
                    $attributes->push($this->makeArtificialColumn($fkName, $model, 'integer')); // Assumption: FKs usually int/uuid
                }

                if ($relationObj instanceof MorphTo) {
                    // MorphTo tem duas colunas: _id e _type
                    $attributes->push($this->makeArtificialColumn($relationObj->getForeignKeyName(), $model)); // *_id
                    $attributes->push($this->makeArtificialColumn($relationObj->getMorphType(), $model, 'string')); // *_type
                }
            } catch (\Throwable $e) {
                // Ignora se der erro ao instanciar relação sem banco
                continue;
            }
        }

        // 8. Consolida (Remove duplicados pelo nome)
        $uniqueAttributes = $attributes->unique('name')->values();

        // 9. Adiciona Atributos Virtuais (Accessors)
        // Passamos a lista de nomes já encontrados para o getVirtualAttributes não duplicá-los
        $knownColumnNames = $uniqueAttributes->pluck('name')->all();
        // Simulamos o array de colunas do DB apenas com os nomes para o método pai
        $simulatedDbColumns = array_map(fn ($n) => ['name' => $n], $knownColumnNames);

        return $uniqueAttributes->merge($this->getVirtualAttributes($model, $simulatedDbColumns));
    }

    /**
     * Cria a estrutura de metadados para uma coluna descoberta via código.
     */
    protected function makeArtificialColumn($name, $model, $fallbackType = 'string')
    {
        return [
            'name' => $name,
            'type' => $this->inferType($name, $model, $fallbackType),
            'increments' => false,
            'nullable' => true, // Sem DB, assumimos true por segurança (exceto PK)
            'default' => null,
            'unique' => false,
            'fillable' => $model->isFillable($name),
            'hidden' => $this->attributeIsHidden($name, $model),
            'appended' => null,
            'cast' => $this->getCastType($name, $model),
        ];
    }

    /**
     * Tenta adivinhar o tipo do dado baseado em Casts ou convenções de nome.
     */
    protected function inferType($name, $model, $default = 'string')
    {
        // 1. Verifica se tem Cast explícito
        $castType = $this->getCastType($name, $model);
        if ($castType) {
            return match ($castType) {
                'int', 'integer' => 'integer',
                'real', 'float', 'double' => 'float',
                'bool', 'boolean' => 'boolean',
                'date', 'datetime', 'timestamp' => 'datetime',
                'array', 'json', 'object', 'collection' => 'json',
                default => 'string',
            };
        }

        // 2. Verifica convenção de nomes (Heurística)
        if (Str::endsWith($name, '_id')) {
            return 'integer'; // Ou 'string' se usar UUIDs, ajuste conforme seu padrão
        }

        if (Str::startsWith($name, 'is_') || Str::startsWith($name, 'has_')) {
            return 'boolean';
        }

        if (Str::endsWith($name, '_at')) {
            return 'datetime';
        }

        return $default;
    }
}
