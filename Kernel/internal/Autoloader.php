<?php

/* This file is part of the Supersoniq project.
 * Supersoniq is a free and unencumbered software released into the public domain.
 * For more information, please refer to <http://unlicense.org/>
 */

namespace Supersoniq\Kernel\Internal;

class Autoloader {



	/*************************************************************************
	  ATTRIBUTES                   
	 *************************************************************************/
        private $library;



	/*************************************************************************
	  CONSTRUCTOR                   
	 *************************************************************************/
        public function init( ) {
		spl_autoload_register( [ $this, 'autoloader' ] );
		$settings      = ( new \Settings )->by_file( 'application' );
		$this->library = $settings->get_array( 'library' );
        }



	/*************************************************************************
	  PRIVATE METHODS                   
	 *************************************************************************/
	private function autoloader( $class_name ) {
		// echo $class_name . HTML_EOL;
		$class = ( new Class_Name )->by_name( $class_name );
		if ( $this->load_library_class( $class_name ) ) {
			return TRUE;
		} else if ( $class->is_parent ) {
			return $this->load_parent_class( $class );
		} else if ( ! is_null( $class->extension ) ) {
			return $this->load_explicit_class( $class );
		} else if ( $this->load_implicit_class( $class, TRUE ) ) {
			return TRUE;
		}
		throw new \Exception( 'Unknown class "' . $class->called_name . '"' );
	}



	/*************************************************************************
	  IMPLICIT PARENT LOADER                   
	 *************************************************************************/
	private function load_library_class( $class_name ) {
		if ( isset( $this->library[ $class_name ] ) ) {
			$file = SUPERSONIQ_ROOT_PATH . $this->library[ $class_name ];
			if ( is_file( $file ) ) {
				require_once( SUPERSONIQ_ROOT_PATH . $this->library[ $class_name ] );
				$this->check_class_loaded( $class_name );
				return TRUE;
			}
		}
		return FALSE;
	}



	/*************************************************************************
	  IMPLICIT PARENT LOADER                   
	 *************************************************************************/
	private function load_parent_class( $class ) {
		if ( $this->load_existing_parent_class( $class ) ) {
			return TRUE;
		}
		if ( $this->create_magic_parent( $class ) ) {
			return TRUE;
		}
		class_alias( 'Supersoniq\Kernel\Internal\Empty_Class', $class->called_name );
		return TRUE;
	}

	private function load_existing_parent_class( $class ) {
		$is_parent_class = FALSE;
		$original_extension = $class->extension;
		$original_full_class_name = $class->full_class_name;
		$subtype = $class->subtype;
		do {
			foreach ( \Supersoniq::$EXTENSIONS as $extension ) {
				$class->extension = \Supersoniq\format_to_namespace( $extension );
				if ( $is_parent_class ) {
					if ( $this->load_existing_explicit_class( $class ) ) {
						class_alias( $class->full_class_name, $class->called_name );
						return TRUE;
					}
				} else if ( $original_full_class_name == $class->full_class_name ) {
					$is_parent_class = TRUE;
				}
			}
			$class->subtype = \Supersoniq\substr_before_last( $class->subtype, '\\' );
		} while ( $class->subtype );
		$class->subtype = $subtype;
		$class->extension = $original_extension;
		return FALSE;
	}



	/*************************************************************************
	  IMPLICIT EXTENSION LOADER                   
	 *************************************************************************/
        public function load_implicit_class( $class, $create_alias = FALSE ) {
		if ( $this->load_existing_implicit_class( $class, $create_alias ) ) {
			return TRUE;
		}
		if ( $this->create_magic_class( $class ) ) {
			return TRUE;
		}
        }

	private function load_existing_implicit_class( $class, $create_alias ) {
		$subtype = $class->subtype;
		do {
			foreach ( \Supersoniq::$EXTENSIONS as $extension ) {
				$class->extension = \Supersoniq\format_to_namespace( $extension );
				if ( $this->load_existing_explicit_class( $class ) ) {
					if ( $create_alias ) {
						$this->create_class( '\\' . $class->full_class_name, $class->called_name );
					}
					return TRUE;
				}
			}
			$class->subtype = \Supersoniq\substr_before_last( $class->subtype, '\\' );
		} while ( $class->subtype );
		$class->subtype = $subtype;
		$class->extension =  NULL;
		return FALSE;
	}



	/*************************************************************************
	  EXPLICIT EXTENSION LOADER                   
	 *************************************************************************/
	private function load_explicit_class( $class ) {
		if ( ! in_array( \Supersoniq\format_to_path( $class->extension ), \Supersoniq::$EXTENSIONS ) ) {
			throw new \Exception( 'Unknown extension "' . $class->extension . '" in "' . $class->called_name . '"' );
		}
		if ( ! $this->load_existing_explicit_class( $class ) ) {
			throw new \Exception( 'Unknown class "' . $class->called_name . '"' );
		}
		return TRUE;
	}

	private function load_existing_explicit_class( $class ) {
		// echo '-- ' . $class->file_path . HTML_EOL;
		if ( is_file( $class->file_path ) ) {
			require_once( $class->file_path );
			$this->check_class_loaded( $class->full_class_name );
			return TRUE;
		}
		return FALSE;
	}

	private function check_class_loaded( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			$classes = get_declared_classes( );
			$loaded_class = end( $classes );
			throw new \Exception\Wrong_Class_Definition( 'Wrong class definition: "' . $loaded_class . '" definition, but "' . $class_name . '" expected.' ); 
		}
	}



	/*************************************************************************
	  MAGIC CLASS CREATION                   
	 *************************************************************************/
	private function create_magic_parent( $class ) {
		if ( $this->create_magic( $class, 'magic_parents' ) ) {
			return TRUE;
		}
		return $this->create_magic_class( $class );
	}

	private function create_magic_class( $class ) {
		return $this->create_magic( $class, 'magic_classes' );
	}

	private function create_magic( $class, $property ) {
		if ( ! $class->is_object ) {
			$settings = ( new \Settings )->by_file( 'application' );
			if ( $settings->has( $property, $class->type ) ) {
				$base_name = $settings->get( $property, $class->type );
				$base_class = '\\__Auto\\' . $class->type . '\\' . $base_name;
				$this->create_class( $base_class, $class->called_name );
				return TRUE;
			}
		}
		return FALSE;
	}

	private function create_class( $base_class, $class ) {
		$namespace = \Supersoniq\substr_before_last( $class, '\\' );
		$name = \Supersoniq\substr_after_last( $class, '\\' );
		$code = '';
		if ( $namespace ) {
			$code = 'namespace ' . $namespace . ';' . PHP_EOL;
		}
		$code .= 'class ' . $name . ' extends ' . $base_class . ' { }' . PHP_EOL;
		// echo $code . HTML_EOL;
		eval( $code );
	}

}

class Empty_Class { 


	/*************************************************************************
	  CONSTRUCTOR                   
	 *************************************************************************/
        public function __construct( ) {
        }
}
