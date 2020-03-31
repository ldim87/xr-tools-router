<?php

/**
 * @author Oleg Isaev (PandCar)
 * @contacts vk.com/id50416641, t.me/pandcar, github.com/pandcar
 */

namespace XrTools;

class RouterMini
{
	/**
	 * @var array
	 */
	protected $go;

	/**
	 * RouterMini constructor.
	 */
	public function __construct()
	{
		$this->go = $this->generateUrlGo();
	}

	/**
	 * @return array
	 */
	public function getGo(): array
	{
		return $this->go;
	}

	/**
	 * @param array $arr_url_go
	 * @param string $path_dir
	 * @param string $take_after
	 * @param array $opt
	 * @return bool|string|array
	 */
	public function getHandlerPath(array $arr_url_go, string $path_dir, string $take_after = '', array $opt = [])
	{
		if (empty($opt['exp_files'])) {
			$opt['exp_files'] = 'php';
		}

		// Если не указано откуда обрезать
		if (empty($take_after)) {
			$list = $arr_url_go;
		}
		else
		{
			$key = array_search($take_after, $arr_url_go);

			if ($key === false) {
				return false;
			}

			$list = array_slice($arr_url_go, $key !== false ? $key + 1 : 0);
		}

		// Если массив пуст то устанавливаем путь по умолчанию
		if (empty($list)) {
			$list = ['index'];
		}

		$nesting = [];
		$result = [];

		foreach ($list as $val)
		{
			$nesting []= $val;
			$path = $path_dir.'/'.implode('/', $nesting).'.'.$opt['exp_files'];

			if (is_file($path)) {
				$result []= $path;
			}
		}

		if (! empty($result)) {
			return ! empty($opt['list']) ? $result : $result[0];
		}

		return false;
	}

	/**
	 * @return array
	 */
	protected function generateUrlGo(): array
	{
		$uri_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

		$uri_path = preg_replace('/(\&|\?).*/', '', $uri_path);

		$uri_path = mb_strtolower(
			mb_substr( trim($uri_path, '/'), 0, 1000)
		);

		$uri_path = preg_replace('/[^a-z0-9\/\-_]/', '', $uri_path);

		return ! empty($uri_path) ? explode('/', $uri_path) : [];
	}
}
