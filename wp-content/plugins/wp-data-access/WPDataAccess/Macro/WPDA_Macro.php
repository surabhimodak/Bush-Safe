<?php

namespace WPDataAccess\Macro {

	class WPDA_Macro {

		protected $raw_code   = null;
		protected $raw_array  = [];
		protected $has_macros = false;

		public function __construct( $raw_code ) {
			$this->raw_code = $raw_code;
			if ( null !== $this->raw_code && '' !== str_replace( ' ', '', $this->raw_code ) ) {
				$this->raw_array = explode( "\n", $this->raw_code );
				if ( is_array( $this->raw_array ) && sizeof( $this->raw_array ) > 2 ) {
					$this->has_macros = true;
				}
			}
		}

		public function exe_macro() {
			if ( $this->has_macros ) {
				$code = $this->macro_if( $this->raw_array );

				return implode( ' ', $code );
			} else {
				return $this->raw_code;
			}
		}

		protected function macro_if( $code_array ) {
			if ( ! is_array( $code_array ) ) {
				return $code_array;
			}

			$codeif   = [];
			$totalifs = 0;

			for ( $line = 0; $line < sizeof( $code_array ); $line++ ) {
				$code = $code_array[ $line ];
				if ( $this->is_macro( 'if', $code ) ) {
					$totalifs++;

					// Process macro if
					$line_start = $line;
					$line_else  = -1;
					$line_end   = $line; // prevent PHP errors
					$ifs_found  = 1;
					for ( $i = $line+1; $i < sizeof( $code_array ); $i++ ) {
						if ( $this->is_macro( 'if', $code_array[ $i ] ) ) {
							$ifs_found++;
						}

						if ( $this->is_macro( 'else', $code_array[ $i ] ) ) {
							if ( 1 === $ifs_found ) {
								$line_else = $i;
							}
						}

						if ( $this->is_macro( 'endif', $code_array[ $i ] ) ) {
							if ( 1 === $ifs_found ) {
								$line_end = $i;
							} else {
								$ifs_found--;
							}
						}
					}

					if ( -1 === $line_else ) {
						// Get code between if and end if
						$code_slice = array_slice( $code_array, $line_start + 1, $line_end - 1 );
					} else {
						// Get code between if and else
						$code_slice = array_slice( $code_array, $line_start + 1, $line_else - 1 );
						// Get code between else and end if
						$code_else = array_slice( $code_array, $line_else + 1, $line_end - $line_else - 1 );
					}

					// Process nested ifs
					$code_slice = $this->macro_if( $code_slice );

					$condition = null;
					foreach ( [ '==','!=','<','>' ] as $c ) { // Handle each condition individually in macro_if_check
						if ( $this->macro_if_has( $code, $c, $condition ) ) {
							if ( $this->macro_if_check( $condition, $c ) ) {
								$codeif = array_merge( $codeif, $code_slice );
							} else {
								if ( $line_else > -1 ) {
									$codeif = array_merge( $codeif, $code_else );
								}
							}
							$line   = $line_end;
							break;
						}
					}
				}
			}

			if ( $totalifs === 0) {
				return $code_array;
			} else {
				return $codeif;
			}
		}

		protected function macro_if_has( $code, $if, &$condition ) {
			$condition = explode( $if, html_entity_decode( substr( str_replace( ' ', '', $code ), 8 ) ) );
			return is_array( $condition ) && sizeof( $condition ) === 2;
		}

		protected function macro_if_check( $condition, $if ) {
			switch ( $if ) {
				case '==';
					return is_array( $condition ) && sizeof( $condition ) === 2 && $condition[0] == $condition[1];
					break;
				case '!=';
					return is_array( $condition ) && sizeof( $condition ) === 2 && $condition[0] != $condition[1];
					break;
				case '<';
					return is_array( $condition ) && sizeof( $condition ) === 2 && $condition[0] < $condition[1];
					break;
				case '>';
					return is_array( $condition ) && sizeof( $condition ) === 2 && $condition[0] > $condition[1];
					break;
			}
		}

		protected function is_macro( $macro, $code ) {
			return "#macro{$macro}" === substr( str_replace( ' ', '', $code ), 0, strlen( "#macro{$macro}" ) );
		}

	}

}