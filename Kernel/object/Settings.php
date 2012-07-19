<?php

/* This file is part of the Supersoniq project.
 * Supersoniq is a free and unencumbered software released into the public domain.
 * For more information, please refer to <http://unlicense.org/>
 */

namespace Supersoniq\Kernel\Object;

class Settings {


	/*************************************************************************
	 ATTRIBUTES
	 *************************************************************************/
	private $extensions = [ ];
	private $file_name;
	public $settings = [ ];



	/*************************************************************************
	  CONSTRUCTOR                 
	 *************************************************************************/
	public function by_file( $file_name ) {
		if ( empty( $this->extensions ) ) {
			$this->extensions = \Supersoniq::$EXTENSIONS;
		}
		$this->file_name = $file_name;
		$this->settings = $this->parse_files( );
		return $this;
	}



	/*************************************************************************
	  SETTINGS METHODS                   
	 *************************************************************************/
	public function extension( $extensions ) {
		\Supersoniq\must_be_array( $extensions );
		$this->extensions = $extensions;
		return $this;
	}



	/*************************************************************************
	  ACCESSOR METHODS                   
	 *************************************************************************/
	public function get_list( $property ) {
		$disabled = [ ];
		$enabled = [ ];
		foreach ( $this->settings as $data ) {
			if ( isset( $data[ $property ][ 'disabled' ] ) ) {
				$new = $data[ $property ][ 'disabled' ];
				$disabled = \Supersoniq\array_merge_unique( $disabled, $new );
			}
			if ( isset( $data[ $property ][ 'enabled' ] ) ) {
				$new = array_diff( $data[ $property ][ 'enabled' ], $disabled );
				$enabled  = \Supersoniq\array_merge_unique( $enabled, $new );
			}
		}
		return $enabled;
	}

	public function get( $section, $property ) {
		foreach ( $this->settings as $data ) {
			if ( isset( $data[ $section ][ $property ] ) ) {
				return $data[ $section ][ $property ];
			}
		}
		return NULL;
	}

	public function get_deep_array( $section, $property ) {
		$deep_array = [ ];
		foreach ( $this->settings as $data ) {
			if ( isset( $data[ $section ][ $property ] ) ) {
				$elements = $data[ $section ][ $property ];
				\Supersoniq\must_be_array( $elements );
				$deep_array = array_merge( $deep_array, $elements );
			}
		}
		return $deep_array;
	}

	public function has( $section, $property ) {
		foreach ( $this->settings as $data ) {
			if ( isset( $data[ $section ][ $property ] ) ) {
				return TRUE;
			}
		}
		return FALSE;
	}



	/*************************************************************************
	  FILE METHODS                   
	 *************************************************************************/
	private function file_paths( ) {
		$file_paths = [ ];
		foreach ( $this->extensions as $extension ) {
			$file_name = $this->file_name;
			if ( \Supersoniq::$PLATFORM_NAME ) {
				$file_name .= '.' . \Supersoniq::$PLATFORM_NAME;
			}
			while ( $file_name ) {
				$file_paths[ ] = $extension . '/settings/' . $file_name . '.ini';
				$file_name = \Supersoniq\substr_before_last( $file_name, '.' );
			}
		}
		return $file_paths;
	}

	private function parse_files( ) {
		$datas = [ ];
		foreach ( $this->file_paths( ) as $file_path ) {
			if ( isset( $this->settings[ $file_path ] ) ) {
				$datas[ $file_path ] = $this->settings[ $file_path ];
			} else {
				$absolute_file_path = SUPERSONIQ_ROOT_PATH . $file_path;
				if ( file_exists( $absolute_file_path ) ) {
					$datas[ $file_path ] = parse_ini_file( $absolute_file_path, TRUE );
				} else {
					$datas[ $file_path ] = NULL;
				}
			}
		}
		return $datas;
	}
}