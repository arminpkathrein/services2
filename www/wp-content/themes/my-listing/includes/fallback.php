<?php

// ACF Pro fallback.
if ( ! class_exists( 'acf' ) && ! is_admin() )
{
	function get_field() { return null; }
	function the_field() { return null; }
	function have_rows() { return null; }
}