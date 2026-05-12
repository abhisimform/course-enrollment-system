<?php

class QueryBuilder
{
  public static function build(array $config)
  {
    $query = $config['query'] ?? $_GET;
    $filterableFields = $config['filterableFields'] ?? $config['allowedFields'] ?? [];
    $searchableFields = $config['searchableFields'] ?? [];
    $allowedSorts = $config['allowedSorts'] ?? [];
    $defaultSort = $config['defaultSort'] ?? array_key_first($allowedSorts) ?? 'id';
    $defaultOrder = $config['defaultOrder'] ?? 'DESC';
    $customFilter = $config['customFilter'] ?? null;
    $deletedColumn = $config['deletedColumn'] ?? null;
    $defaultLimit = (int)($config['defaultLimit'] ?? 10);
    $maxLimit = (int)($config['maxLimit'] ?? 100);
    $searchKey = $config['searchKey'] ?? 'search';
    $sortKey = $config['sortKey'] ?? 'sortBy';
    $orderKey = $config['orderKey'] ?? 'order';
    $deletedKey = $config['deletedKey'] ?? 'deleted';
    $baseWhere = $config['baseWhere'] ?? [];
    $baseBindings = $config['baseBindings'] ?? [];

    $where = is_array($baseWhere) ? array_values($baseWhere) : [$baseWhere];
    $bindings = $baseBindings;

    foreach ($filterableFields as $inputKey => $fieldConfig) {
      $field = self::normalizeFilterField($inputKey, $fieldConfig);

      if (!array_key_exists($field['input'], $query) || $query[$field['input']] === '') {
        continue;
      }

      $param = ':' . $field['param'];
      $value = self::normalizeFilterValue($query[$field['input']], $field['type']);

      if ($field['operator'] === 'LIKE') {
        $value = '%' . $value . '%';
      }

      $where[] = "{$field['column']} {$field['operator']} {$param}";
      $bindings[$param] = $value;
    }

    if (!empty($query[$searchKey]) && $searchableFields) {
      $searchParts = [];
      $searchValue = trim((string)$query[$searchKey]);

      foreach ($searchableFields as $index => $field) {
        $param = ":search_$index";
        $searchParts[] = "$field LIKE $param";
        $bindings[$param] = '%' . $searchValue . '%';
      }

      $where[] = '(' . implode(' OR ', $searchParts) . ')';
    }

    if ($deletedColumn) {
      $deletedMode = self::normalizeDeletedMode($query[$deletedKey] ?? null);

      if ($deletedMode === 'only') {
        $where[] = "{$deletedColumn} IS NOT NULL";
      } elseif ($deletedMode !== 'with') {
        $where[] = "{$deletedColumn} IS NULL";
      }
    }

    if (is_callable($customFilter)) {
      call_user_func_array($customFilter, [
        $query,
        &$where,
        &$bindings
      ]);
    }

    $sortBy = $query[$sortKey] ?? $defaultSort;
    $order = strtoupper($query[$orderKey] ?? $defaultOrder);
    $order = in_array($order, ['ASC', 'DESC']) ? $order : 'DESC';
    $sortBy = $allowedSorts[$sortBy] ?? $defaultSort;
    $orderBy = "$sortBy $order";

    $limit = isset($query['limit']) ? (int)$query['limit'] : $defaultLimit;
    $limit = $limit > 0 ? min($limit, $maxLimit) : $defaultLimit;
    $page = isset($query['page']) ? (int)$query['page'] : 1;
    $page = max(1, $page);
    $offset = ($page - 1) * $limit;
    $joins = $config['joins'] ?? [];

    return [
      'joins' => $joins,
      'whereSql' => $where ? implode(' AND ', $where) : '1=1',
      'bindings' => $bindings,
      'orderBy' => $orderBy,
      'limit' => $limit,
      'offset' => $offset,
      'page' => $page,
      'query' => $query
    ];
  }

  private static function normalizeFilterField($inputKey, $fieldConfig)
  {
    if (is_string($fieldConfig)) {
      return [
        'input' => $inputKey,
        'column' => $inputKey,
        'type' => $fieldConfig,
        'operator' => $fieldConfig === 'string' ? 'LIKE' : '=',
        'param' => self::sanitizeParamName($inputKey)
      ];
    }

    return [
      'input' => $fieldConfig['input'] ?? $inputKey,
      'column' => $fieldConfig['column'] ?? $inputKey,
      'type' => $fieldConfig['type'] ?? 'string',
      'operator' => strtoupper($fieldConfig['operator'] ?? (($fieldConfig['type'] ?? 'string') === 'string' ? 'LIKE' : '=')),
      'param' => $fieldConfig['param'] ?? self::sanitizeParamName($fieldConfig['input'] ?? $inputKey)
    ];
  }

  private static function normalizeFilterValue($value, $type)
  {
    return match ($type) {
      'int' => (int)$value,
      'bool' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
      'string' => trim((string)$value),
      default => $value
    };
  }

  private static function sanitizeParamName($value)
  {
    return preg_replace('/[^a-zA-Z0-9_]/', '_', $value);
  }

  private static function normalizeDeletedMode($value)
  {
    if ($value === null || $value === '' || $value === false) {
      return 'without';
    }

    $normalized = strtolower(trim((string)$value));

    if (in_array($normalized, ['with', 'all'], true)) {
      return 'with';
    }

    if (in_array($normalized, ['only', 'deleted'], true)) {
      return 'only';
    }

    if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
      return 'with';
    }

    return 'without';
  }
}
