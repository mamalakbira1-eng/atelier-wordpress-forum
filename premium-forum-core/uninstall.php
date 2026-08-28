<?php
/**
 * Premium Forum Core — politique de désinstallation non destructive.
 *
 * Les tables pfc_* et les données de forum sont conservées volontairement afin
 * qu'une désinstallation ne provoque aucune perte de données. Seules les
 * options techniques propres au plugin sont retirées.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'pfc_schema_version' );
delete_option( 'pfc_schema_last_error' );
delete_option( 'pfc_community_data_version' );
