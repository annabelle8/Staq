<?php

/* This file is part of the Supersoniq project.
 * Supersoniq is a free and unencumbered software released into the public domain.
 * For more information, please refer to <http://unlicense.org/>
 */

namespace Supersoniq\Kernel\View\Layout;

class Simple extends Simple\__Parent {



	/*************************************************************************
	  PRIVATE METHODS                   
	 *************************************************************************/
	protected function fill( $template ) {
		$template->application_name = \Supersoniq::$APPLICATION_NAME;
		return $template;
	}
}
