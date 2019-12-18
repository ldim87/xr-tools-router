<?php
/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools;

/**
 * RouterException 
 */
class RouterException extends \Exception {
	/**
	 * Swapped code and message. Use to throw direct http codes
	 * @param int         $code    HTTP code
	 * @param string|null $message Optional message
	 */
	function __construct(int $code, string $message = null){
		// make sure everything is assigned properly
        parent::__construct($message ?? 'Http code ' . $code, $code);
	}

}
