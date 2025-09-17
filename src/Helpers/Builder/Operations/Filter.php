<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers\Builder\Operations;

use Illuminate\Database\Eloquent\Builder;
use Victormgomes\Queryparams\Enums\Operators;

class Filter
{
    public static function build(Builder $query, string $field, string $operator, $value): void
    {
        switch ($operator) {
            case Operators::EQ:
                $query->where($field, $value);
                break;

            case Operators::NE:
                $query->where($field, '!=', $value);
                break;

            case Operators::GT:
                $query->where($field, '>', $value);
                break;

            case Operators::GTE:
                $query->where($field, '>=', $value);
                break;

            case Operators::LT:
                $query->where($field, '<', $value);
                break;

            case Operators::LTE:
                $query->where($field, '<=', $value);
                break;

            case Operators::IN:
                $query->whereIn($field, (array) $value);
                break;

            case Operators::NIN:
                $query->whereNotIn($field, (array) $value);
                break;

            case Operators::NULL:
                $query->whereNull($field);
                break;

            case Operators::NOTNULL:
                $query->whereNotNull($field);
                break;

            case Operators::BETWEEN:
                if (is_array($value) && count($value) === 2) {
                    $query->whereBetween($field, $value);
                }
                break;

            case Operators::NBETWEEN:
                if (is_array($value) && count($value) === 2) {
                    $query->whereNotBetween($field, $value);
                }
                break;

            case Operators::LIKE:
                $query->where($field, 'like', "%{$value}%");
                break;

            case Operators::NOTLIKE:
                $query->where($field, 'not like', "%{$value}%");
                break;

            case Operators::ILIKE:
                $query->whereRaw("LOWER({$field}) LIKE LOWER(?)", ["%{$value}%"]);
                break;

            case Operators::NOTILIKE:
                $query->whereRaw("LOWER({$field}) NOT LIKE LOWER(?)", ["%{$value}%"]);
                break;

            case Operators::CONTAINS:
                $query->whereJsonContains($field, $value);
                break;

            case Operators::CONTAINEDBY:
                $query->whereRaw("? <@ {$field}", [$value]);
                break;

            case Operators::OVERLAP:
                $query->whereRaw("? && {$field}", [$value]);
                break;

            case Operators::FTS:
                $query->whereRaw("to_tsvector({$field}) @@ plainto_tsquery(?)", [$value]);
                break;

            default:
                break;
        }
    }
}
