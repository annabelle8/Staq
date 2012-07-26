<?php

namespace Supersoniq\Kernel\View;

class __Base {



	/*************************************************************************
	 ATTRIBUTES
	 *************************************************************************/
	public $type;



	/*************************************************************************
	  CONSTRUCTOR                   
	 *************************************************************************/
	public function __construct( ) {
		$this->type = \Supersoniq\class_type_name( $this );
	}



	/*************************************************************************
	  PUBLIC METHODS                   
	 *************************************************************************/
	public function render( ) {
		$template = ( new \Template )->by_name( $this->type );
		if ( ! $template->is_template_found( ) ) {
			throw new \Exception( 'Template not found "' . $this->type . '"' );
		}
		return $template->render( );
	}
}
