<?php

namespace Tribe\Project\Shortener;


use Tribe\Libs\Schema\Schema;

class Database extends Schema {

	const SHORTENER_TABLE = 'shortened_urls';

	protected $schema_version = 1;

	/**
	 * Register the table with wpdb.
	 *
	 * @action init
	 */
	public function register() {
		global $wpdb;

		$wpdb->ms_global_tables[]      = self::SHORTENER_TABLE;
		$wpdb->{self::SHORTENER_TABLE} = $wpdb->base_prefix . '_' . self::SHORTENER_TABLE;
	}

	public function get_updates() {
		return [
			1 => [ $this, 'create_table' ],
		];
	}

	public function create_table() {
		global $wpdb;
		$wpdb_collate = $wpdb->collate;

		$sql =
			"CREATE TABLE {$this->table_name()} (
         id varchar(8) NOT NULL,
         url varchar(2083) NOT NULL,
         PRIMARY KEY  (id),
         KEY first (url)
         )
         COLLATE {$wpdb_collate}";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	private function table_name() {
		global $wpdb;

		return $wpdb->base_prefix . self::SHORTENER_TABLE;
	}

	public function get_shortened_url( $url ) {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$this->table_name()} WHERE url = %s",
				$url )
		);
	}

	public function save_url( $id, $url ) {
		global $wpdb;

		$wpdb->insert(
			$this->table_name(),
			[
				'id'  => $id,
				'url' => $url,
			]
		);

		if ( $wpdb->last_error ) {
			throw new \Exception( 'Non-unique ID' );
		}
	}

	public function get_url( $id ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare(
			"SELECT url FROM {$this->table_name()} WHERE id = %s"
			, $id )
		);
	}

}