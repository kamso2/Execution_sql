<?php
/**
 * includes/query_builder.php
 * Fonction centralisée pour construire la requête SQL finale
 * utilisée par api_execute.php et api_export.php
 */

function buildSqlQuery($query_config, $inputs) {
    $sql = $query_config['template'];
    $execution_params = [];
    $where_clauses = [];

    // 1. Remplacements structurels (Table / Colonnes)
    foreach (['table', 'columns'] as $placeholder) {
        $search = "%$placeholder%";
        // Si le placeholder n'est pas dans le template, on passe
        if (strpos($sql, $search) === false) continue;

        $user_choice = $inputs[$placeholder] ?? null;
        $map = $query_config['replacement_maps'][$placeholder] ?? [];

        if (!isset($map[$user_choice])) {
            throw new Exception("Paramètre obligatoire manquant ou invalide : $placeholder");
        }
        $sql = str_replace($search, $map[$user_choice], $sql);
    }

    // 2. Filtres Conditionnels
    
    // A. Filtre Valeur Exacte (Condition Column)
    $cond_col = $inputs['condition_column'] ?? null;
    // Gérer le fait que 'params' peut être sous-tableau (api_execute) ou plat (api_export)
    $cond_val = $inputs['params']['value'] ?? $inputs['value'] ?? null;

    if ($cond_col && $cond_val !== '' && isset($query_config['replacement_maps']['condition_column'][$cond_col])) {
        $sql_col = $query_config['replacement_maps']['condition_column'][$cond_col];
        $where_clauses[] = "$sql_col = :value";
        $execution_params[':value'] = $cond_val;
    }

    // B. Filtre Date
    $date_col = $inputs['date_column'] ?? null;
    $start_date = $inputs['params']['start_date'] ?? $inputs['start_date'] ?? null;
    $end_date = $inputs['params']['end_date'] ?? $inputs['end_date'] ?? null;

    if ($date_col && isset($query_config['replacement_maps']['date_column'][$date_col])) {
        $sql_date_col = $query_config['replacement_maps']['date_column'][$date_col];
        
        if ($start_date && $start_date !== '') {
            $where_clauses[] = "$sql_date_col >= :start_date";
            $execution_params[':start_date'] = $start_date;
        }
        if ($end_date && $end_date !== '') {
            $where_clauses[] = "$sql_date_col <= :end_date";
            $execution_params[':end_date'] = $end_date . ' 23:59:59';
        }
    }

    // 3. Assemblage WHERE
    if (!empty($where_clauses)) {
        // Détecter si le template contient déjà un WHERE (insensible à la casse)
        if (stripos($sql, " WHERE ") !== false) {
            $sql .= " AND " . implode(" AND ", $where_clauses);
        } else {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }
    }

    // 4. Injection des paramètres directs (pdo_params) définis dans query_config
    if (isset($query_config['pdo_params'])) {
        foreach ($query_config['pdo_params'] as $p) {
            // Chercher dans les inputs ou params imbriqués
            $val = $inputs[$p] ?? $inputs['params'][$p] ?? null;
            if ($val !== null) {
                $execution_params[":$p"] = $val;
            }
        }
    }

    return [
        'sql' => $sql,
        'params' => $execution_params
    ];
}
?>
