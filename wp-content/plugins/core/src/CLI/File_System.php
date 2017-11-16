<?php

namespace Tribe\Project\CLI;

class File_System {

	private $config_file = '';

	public function create_directory( $directory ) {
		clearstatcache();
		if ( file_exists( $directory ) ) {
			\WP_CLI::error( 'Sorry... ' . $directory . ' directory already exists' );
		}
		if ( ! mkdir( $directory ) && ! is_dir( $directory ) ) {
			\WP_CLI::error( 'Sorry...something went wrong when we tried to create ' . $directory );
		}
	}

	public function write_file( $file, $contents, $overwrite = false ) {
		if ( file_exists( $file) && ! $overwrite ) {
			\WP_CLI::error( 'Sorry... ' . $file . ' already exists.' );
		}
		if ( ! $handle = fopen( $file, 'w' ) ) {
			\WP_CLI::error( 'Sorry...something went wrong when we tried to write to ' . $file );
		}

		fwrite( $handle, $contents );
		return $handle;
	}

	public function insert_into_existing_file( $file, $new_line, $below_line, $replace = false ) {
		if ( ! $handle = fopen( $file, 'r+' ) ) {
			\WP_CLI::error( 'Sorry.. ' . $file . ' could not be opened.' );
		}

		$contents = '';
		while (! feof ( $handle ) ) {
			$match = false;
			$line = fgets( $handle );
			if ( strpos( $line, $below_line ) !== false ) {
				$match = true;
			}
			
			if ( ! $match || ( $match && ! $replace ) ) {
				$contents .= $line;
			}

			if ( $match ) {
				$contents .= $new_line;
			}

		}

		if ( ! fclose( $handle ) ) {
			\WP_CLI::error( 'Sorry.. ' . $file . ' an error has occurred.' );
		}

		$this->write_file( $file, $contents, true );
	}

	public function get_file( $path ) {
		return file_get_contents( $path );
	}

	public function get_site_root_path() {
		for ( $i = 1; ; $i ++ ) {
			if ( file_exists( dirname( __DIR__, $i ) . '/wp-config.php' ) ) {
				return trailingslashit( dirname( __DIR__, $i ) );
			}
		}
	}

	public function get_config_file() {
		if ( ! empty( $this->config_file ) ) {
			return $this->config_file;
		}

		$site_root = $this->get_site_root_path();

		$this->config_file = $site_root . 'wp-config.php';

		if ( file_exists( $site_root . 'local-config.php' ) ) {
			$this->config_file = $site_root . 'local-config.php';
		}

		return $this->config_file;

	}

}
