<?php
/**
 * Test de la requête STOP_DATE
 * Ce script teste la génération de la requête SQL pour STOP_DATE
 */

// Simuler les includes
$COMMON_TABLES = [
    "repeteurs" => "FRAGILE_REPETEUR",
];

$COMMON_DATE_MAP = [
    "fin_abonnement" => "STR_TO_DATE(date_fin_abonnement, '%d/%m/%Y')",
];

$query_config = [
    "template" => "UPDATE %table% SET STATUS = 'COMPLETED' WHERE STATUS != 'COMPLETED'",
    "replacement_maps" => [
        "table"       => $COMMON_TABLES,
        "date_column" => $COMMON_DATE_MAP
    ],
    "pdo_params" => []
];

// Simuler les inputs utilisateur
$inputs = [
    "table" => "repeteurs",
    "date_column" => "fin_abonnement",
    "params" => [
        "end_date" => "2026-02-11"
    ]
];

// Inclure le query_builder
require 'includes/query_builder.php';

try {
    $result = buildSqlQuery($query_config, $inputs);
    
    echo "=== TEST STOP_DATE ===\n\n";
    echo "SQL généré :\n";
    echo $result['sql'] . "\n\n";
    
    echo "Paramètres :\n";
    print_r($result['params']);
    
    echo "\n=== RÉSULTAT ATTENDU ===\n";
    echo "SQL : UPDATE FRAGILE_REPETEUR SET STATUS = 'COMPLETED' WHERE STATUS != 'COMPLETED' AND STR_TO_DATE(date_fin_abonnement, '%d/%m/%Y') <= :end_date\n";
    echo "Params : Array ( [:end_date] => 2026-02-11 23:59:59 )\n";
    
    echo "\n✅ Test réussi !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>
