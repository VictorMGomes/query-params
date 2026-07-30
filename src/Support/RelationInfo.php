<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

final class RelationInfo
{
    public function __construct(
        public readonly string $realName,
        public readonly string $type,
        public readonly string $related,
        public readonly ?string $foreignKey = null,
        public readonly bool $isAlias = false,
        public readonly bool $isFk = false,
    ) {}

    /** @param Relation<\Illuminate\Database\Eloquent\Model, *, *> $relation */
    public static function fromRelation(Relation $relation, string $realName): self
    {
        $foreignKey = $relation instanceof BelongsTo ? $relation->getForeignKeyName() : null;

        return new self(
            realName: $realName,
            type: class_basename($relation),
            related: class_basename($relation->getRelated()),
            foreignKey: $foreignKey,
        );
    }

    public function withAlias(bool $isFk = false): self
    {
        return new self(
            realName: $this->realName,
            type: $this->type,
            related: $this->related,
            foreignKey: $this->foreignKey,
            isAlias: true,
            isFk: $isFk,
        );
    }
}
