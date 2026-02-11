<?php
/**
 * includes/query_config.php - Configuration centralisée des requêtes SQL
 */

// 1. CONFIGURATION COMMUNE (FACTORISATION)
// -----------------------------------------------------------------------------

// Liste maîtresse des tables avec leurs labels techniques BDD
$COMMON_TABLES = [
    "repeteurs"                  => "FRAGILE_REPETEUR",
    "retour_echus"               => "RETOUR_ECHUS",
    "one_three_months"           => "ECHUS_1_3_MOIS",
    "welcome_call"               => "WC_QUALIFICATION_RECRUTES",
    "access_evasion_tc_inactifs" => "ACCESS_EVASION_TOUT_CANAL_INACTIFS",
    "echus_3_4_mois"             => "ECHUS_3_4_MOIS",
    "evasion_actifs"             => "EVASION_ACTIFS",
    "fragile_retour_echus"       => "FRAGILE_RETOUR_ECHUS_",
    "g11_bundle"                 => "G11_BUNDLE",
    "insight_plus"               => "INSIGHT_PLUS",
    "netflix_inactif"            => "NETFLIX_INACTIF",
    "project_g11"                => "PROJECT_G11",
    "reconquete_access_evasion"  => "RECONQUETE_ACCESS_EVASION_TC_INACTIFS",
    "service_plus"               => "SERVICE_PLUS",
    "ultimate_evasion_plus"      => "ULTIMATE_EVASION_PLUS",
    "ultimate_toutcanal_plus"    => "ULTIMATE_TOUTCANAL_PLUS",
    "upgrade_access_evasion"     => "UPGRADE_ACCESS_EVASION_MOMO_RP",
    "welcome_g11"                => "WELCOME_G11",
];

// Configuration commune pour les dates (souvent identique pour plusieurs rapports)
$COMMON_DATE_MAP = [
    "fin_abonnement" => "STR_TO_DATE(date_fin_abonnement, '%d/%m/%Y')",
];

// 2. DÉFINITION DES REQUÊTES
// -----------------------------------------------------------------------------
$queries = [
    /**
     * Rapport : NOMBRE DE FICHES
     */
    "nombre_de_fiches" => [
        "template" => "SELECT * FROM %table%",
        "replacement_maps" => [
            "table"            => $COMMON_TABLES,
            "columns"          => ["all" => "*"],
            "condition_column" => ["status" => "STATUS"],
            "date_column"      => $COMMON_DATE_MAP
        ],
        "pdo_params" => ["value"]
    ],

    /**
     * Rapport : RELANCE (Mise à jour en masse du statut)
     */
    "RELANCE" => [
        "template" => "UPDATE %table% SET STATUS = :new_status WHERE STATUS = :value",
        "replacement_maps" => [
            "table" => $COMMON_TABLES,
            "date_column" => $COMMON_DATE_MAP
        ],
        "allowed_values" => ['COMPLETED', 'NOT_DIALED', 'CALLBACK', ' '],
        "pdo_params" => ["value", "new_status"]
    ],

    /**
     * Rapport : AJOUT DATE (Reset des tentatives sur une période)
     */
    "AJOUT_DATE" => [
        "template" => "UPDATE %table% SET nbattempts = '0', STATUS = 'NOT_DIALED' WHERE nbattempts != '15' AND STATUS = :value",
        "replacement_maps" => [
             "table"       => $COMMON_TABLES,
             "date_column" => $COMMON_DATE_MAP
        ],
        "pdo_params" => ["value"]
    ],

    /**
     * Rapport : STOP DATE (Marquer comme COMPLETED selon date fin abonnement)
     */
    "STOP_DATE" => [
        "template" => "UPDATE %table% SET STATUS = 'COMPLETED' WHERE STATUS != 'COMPLETED'",
        "replacement_maps" => [
            "table"       => $COMMON_TABLES,
            "date_column" => $COMMON_DATE_MAP
        ],
        "pdo_params" => []
    ],
];
?>
