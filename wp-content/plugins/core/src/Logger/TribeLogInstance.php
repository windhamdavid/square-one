<?php
namespace Tribe\Project\Logger;

interface TribeLogInstance {
	public function get_label();
	public function register_logger( $debug_level );
}