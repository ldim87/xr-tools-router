<?
/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools;

/**
 * Custom URL router
 * 
 * :TODO:REFACTOR: translate docs
 */
class Router {

	/**
	 * 
	 * @var integer
	 */
	private $maxUrlLength = 1000;

	/**
	 * 
	 * @var string
	 */
	private $url;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * 
	 * @var array
	 */
	private $urlParts = [];

	/**
	 * @param Config $config [description]
	 */
	function __construct(Config $config){
		$this->config = $config;
	}

	/**
	 * [setUrl description]
	 * @param string $url [description]
	 */
	public function setUrl(string $url = null) {

		if(!isset($url)){
			if($queryParam = $this->config->get('urlFromQueryParam')){
				$url = $_GET[$queryParam] ?? null;
			} else {
				$url = $this->config->get('url');
			}
		}

		$this->url = $this->sanitizeUrl($url);

		// sanitized version
		if($url !== $this->url){
			throw new RouterException(301, $this->makeUrl($this->url));
		}
	
		// index (canonical redirect)
		if($url == 'index'){
			throw new RouterException(301, '/');
		}

		$this->urlParts = explode('/', $this->url);
	}

	/**
	 * [getUrl description]
	 * @return [type] [description]
	 */
	public function getUrl(){
		// return page url
		return $this->url;
	}
	
	/**
	 * [getUrlParts description]
	 * @return [type] [description]
	 */
	public function getUrlParts(): array {
		// get exploded url
		return $this->urlParts;
	}

	/**
	 * Формирует новый относительный УРЛ с имеющимися _GET параметрами, если не задано иначе
	 * @param  string 		$new_location 	Desired URL path without a leading slash
	 * @param  array|NULL 	$query_params 	Forced URL query params or _GET if NULL (default)
	 * @return string       				new URL path
	 */
	public function makeUrl(string $newLocation, array $newQueryParams = NULL){
		// get filtered $_GET
		$query_params = $this->getUrlQuery($newQueryParams);
		
		return '/' . $newLocation . ($query_params ? '?' . http_build_query($query_params) : '');
	}

	/**
	 * Возвращает офильтрованный от системных ключей $_GET массив
	 * @param  boolean $flush_cache By default result array is cached in $GLOBALS['_GET_']. This option forces to flush this cache. Default: false
	 * @return array                Filtered array
	 */
	public function getUrlQuery(array $queryParams = null){
		// get params source
		$tmp = $queryParams ?? $_GET;

		$urlQueryIgnoreParams = [];

		if($skip = $this->config->get('urlFromQueryParam')){
			$urlQueryIgnoreParams[] = $skip;
		}

		foreach ($urlQueryIgnoreParams as $value) {
			if(isset($tmp[$value])){
				unset($tmp[$value]);
			}
		}

		return $tmp;
	}

	/**
	 * Убирает из текущих параметров УРЛ указанный параметр или список параметров
	 * @param  string|array $skip_key 	Key name or key names in array
	 * @return string           		New URL query string
	 */
	public function getUrlQueryWithoutParams($skipQueryParams){
		// filtered _GET params
		$get_params = $this->getUrlQuery();
		$new_uri_params = [];

		foreach($get_params as $key => $value){
			// пропускаем исключенное
			if(is_array($skipQueryParams) && in_array($key, $skipQueryParams)){
				continue;
			}
			elseif($key == $skipQueryParams){
				continue;
			}

			$new_uri_params[$key] = $value;
		}

		return '?' . ($new_uri_params ? http_build_query($new_uri_params) . '&' : '');
	}

	/**
	 * Возвращает запрашиваемую часть УРЛ (индекс в $this->urlParts). Можно использовать фильтр номеров страниц
	 * @param  integer        $part         URL part index from $this->urlParts (URL array exploded by slashes)
	 * @param  boolean        $page_number  Check if $part is number and return integer ("1" on error or if part is not found). Default: false (do not use filter)
	 * @return string|integer|boolean       URL part string or FALSE if not found. If $page_number is set to TRUE, then integer is returned ("1" if not found or wrong format)
	 */
	public function getUrlPart(int $part, bool $isPageNumber = false){
		// page number mode
		if($isPageNumber){

			$default_page = 1;

			$url_part = $this->urlParts[$part] ?? $default_page;

			return '0'.$url_part == $url_part && $url_part > 0 ? ((int) $url_part) : $default_page;
		}

		return $this->urlParts[$part] ?? false;
	}

	public function shiftUrlParts(){
		return array_shift($this->urlParts);
	}

	/**
	 * @param  string|null $url [description]
	 * @return [type]           [description]
	 */
	private function sanitizeUrl(string $url = null){
		
		if(!isset($url)){
			throw new RouterException(503, 'Request not set!');
		}
		
		if(!strlen($url)){
			return '';
		}
		
		return rtrim(preg_replace('/[^a-z0-9\/\-_]/', '', strtolower(substr($url, 0, $this->maxUrlLength))), '/');
	}
}
