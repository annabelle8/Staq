<?php

/* This file is part of the Staq project, which is under MIT license */


namespace Staq\Core\Ground\Stack;

class Setting {



	/*************************************************************************
	  ATTRIBUTES         
	 *************************************************************************/
	static public $initialized = FALSE;
	static public $cache = [ ]; 
	static public $cache_file;



	/*************************************************************************
	  CONSTRUCTOR             
	 *************************************************************************/
	public function __construct( ) {
		if ( ! static::$initialized ) {
			$this->initialize( );
		}
	}
	
	protected function initialize( ) {
		if ( $path = \Staq::App()->get_path( 'cache/', TRUE ) ) {
			static::$cache_file = $path . '/setting.' . \Staq::App()->get_platform( ) . '.php';
			if ( is_file( static::$cache_file ) ) {
				require( static::$cache_file );
				if ( is_array( $cache ) ) {
					static::$cache = $cache;
				}
			}
		}
		static::$initialized = TRUE;
		return $this;
	}



	/*************************************************************************
	  CACHE METHODS              
	 *************************************************************************/
	public function clear_cache( ) {
		static::$cache = [ ];
		static::$cache_file = NULL;
		$this->initialize( );
		return $this;
	}

	protected function has_cache( $setting_file_name ) {
		return isset( static::$cache[ $setting_file_name ] );
	}

	protected function add_cache( $setting_file_name, $settings ) {
		$settings = $settings->getArrayCopy( );
		static::$cache[ $setting_file_name ] = $settings;
		if ( \Staq::App( )->isInitialized( ) ) {
			$setting = ( new $this )->parse( 'Application' );
			if ( $setting->getAsBoolean( 'cache.setting' ) ) {
				if ( 
					! static::$cache_file ||
					! $handle = @fopen( static::$cache_file, 'a' )
				) {
					return NULL;
				}
				if ( 0 == filesize( static::$cache_file ) ) {
					fwrite( $handle, '<?php' . PHP_EOL . '$cache = array( );' . PHP_EOL );
				}
				fwrite( $handle, '$cache["' . $setting_file_name . '"] = ' . var_export( $settings, TRUE ) . ';' . PHP_EOL );	
				fclose($handle);
			}
		}
	}

	protected function get_cache( $setting_file_name ) {
		return new \Pixel418\Iniliq\ArrayObject( static::$cache[ $setting_file_name ] );
	}



	/*************************************************************************
	  PARSE METHODS              
	 *************************************************************************/
	public function parse( $mixed ) {
		if ( \Staq\Util::isStack( $mixed ) ) {
			return $this->parse_from_stack( $mixed );
		}
		return $this->parse_fromString( $mixed );
	}

	protected function parse_from_stack( $stack ) {
		$setting_file_name = $this->get_setting_file_name_from_stack( $stack );
		return $this->parse_fromString( $setting_file_name );
	}

	protected function parse_fromString( $setting_file_name ) {
		\UString::doSubstrBefore( $setting_file_name, '.' );
		if ( ! $this->has_cache( $setting_file_name ) ) {
			$file_paths = $this->get_file_paths( $setting_file_name );
			$stack = 'Stack\\' . \Staq\Util::convertPathToNamespace( $setting_file_name );
			foreach( \Staq\Util::getStackDefinition( $stack ) as $class ) {
				if ( isset( $class::$setting ) ) {
					array_unshift( $file_paths, $class::$setting );
				}
			}
			$settings = ( new \Pixel418\Iniliq\Parser )->parse( $file_paths );
			$this->add_cache( $setting_file_name, $settings );
		}
		return $this->get_cache( $setting_file_name );
	}

	protected function get_setting_file_name_from_stack( $stack ) {
		$setting_file_name = \Staq\Util::getStackQuery( $stack );
		return \Staq\Util::convertNamespaceToPath( $setting_file_name );
	}

	protected function get_file_paths( $full_setting_file_name ) {
		$file_names = $this->get_file_names( $full_setting_file_name );
		$platform_name = \Staq::App()->get_platform( );
		$file_paths = [ ];
		foreach ( \Staq::App()->get_extensions( ) as $extension ) {
			foreach( $file_names as $file_name ) {
				if ( $platform_name ) {
					$file_name .= '.' . $platform_name;
				}
				while ( $file_name ) {
					$path = realpath( $extension . '/setting/' . $file_name . '.ini' );
					if ( $path ) {
						$file_paths[ ] = $path;
					}
					$file_name = \UString::substrBeforeLast( $file_name, '.' );
				}
			}
		}
		return array_reverse( $file_paths );
	}

	protected function get_file_names( $file_name ) {
		$file_names = [ ];
		do {
			$file_names[ ] = $file_name;
			$file_name = \UString::substrBeforeLast( $file_name, '/' );
		} while( ! empty( $file_name ) );
		return $file_names;
	}
}
