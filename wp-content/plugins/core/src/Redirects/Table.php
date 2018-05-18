<?php

namespace Tribe\Project\Redirects;

use Tribe\Project\Util\Table_Maker;

/**
 * Class Table
 * @package Tribe\Project\Redirects
 */
class Table extends Table_Maker {

	const NAME = 'tribe_redirects';

	protected $schema_version = '0.1.0';

	protected $tables = [ self::NAME ];

	protected function get_table_definition( $table ) {
		global $wpdb;
		$table_name = $wpdb->table;
		$charset_collate = $wpdb->get_charset_collate();

		switch ( $table ) {
			case self::NAME :
				return "CREATE TABLE {$table_name} (
						redirect_id BIGINT(20) unsigned NOT NULL,
						pattern VARCHAR(255),
						is_regex TINYINT DEFAULT 0,
						redirect_url VARCHAR(255),
						is_page TINYINT DEFAULT 0,
						page_id BIGINT(20),
						PRIMARY KEY (redirect_id)
					  	) $charset_collate";
		}
	}

}