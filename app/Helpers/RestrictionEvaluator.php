<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class RestrictionEvaluator
{
  public static function evaluate(?array $restrictions, array $context = []): bool
  {
    if (!$restrictions || empty($restrictions['conditions'])) {
      // No restrictions = always apply
      return true;
    }

    $behavior = $restrictions['behavior'] ?? 'APPLY_ONLY_IF_MATCHED';
    $logic = strtolower($restrictions['logic'] ?? 'and');
    $conditions = $restrictions['conditions'] ?? [];

    $matches = match ($logic) {
      'or' => collect($conditions)->contains(fn($condition) => self::evaluateCondition($condition, $context)),
      default => collect($conditions)->every(fn($condition) => self::evaluateCondition($condition, $context)),
    };

    return match ($behavior) {
      'APPLY_ONLY_IF_MATCHED' => $matches,
      'APPLY_UNLESS_MATCHED' => !$matches,
      default => true,
    };
  }

  // Evaluate a single condition against the context
  protected static function evaluateCondition(array $condition, array $context): bool
  {
    $modelKey = $condition['model'] ?? null;
    $property = $condition['property'] ?? null;
    $operator = $condition['operator'] ?? 'equals';
    $expected = $condition['value'] ?? null;

    if (!$modelKey || !$property) {
      return false;
    }

    $model = Arr::get($context, $modelKey);

    if ($model instanceof Model) {
      $actual = $model->{$property} ?? null;
    } elseif (is_array($model)) {
      $actual = Arr::get($model, $property);
    } else {
      return false;
    }

    return self::compare($actual, $operator, $expected);
  }

  // Compare actual and expected values based on the operator
  protected static function compare($actual, string $operator, $expected): bool
  {
    return match ($operator) {
      'equals' => $actual == $expected,
      'not_equals' => $actual != $expected,
      'in' => is_array($expected) && in_array($actual, $expected),
      'not_in' => is_array($expected) && !in_array($actual, $expected),
      'gte' => is_numeric($actual) && $actual >= $expected,
      'lte' => is_numeric($actual) && $actual <= $expected,
      default => false,
    };
  }
}
