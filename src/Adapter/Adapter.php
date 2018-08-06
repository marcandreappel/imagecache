<?php
/**
 * Adapter.php
 * @author      Marc-André Appel <marc-andre@appel.fun>
 * @copyright   2018 Marc-André Appel
 * @license     http://opensource.org/licenses/LGPL-3.0 LGPL-3.0
 * @link        https://github.com/marcandreappel/imagecache
 * @created     03/08/2018
 */

namespace MarcAndreAppel\ImageCache\Adapter;

use League\Flysystem\Filesystem;

abstract class Adapter
{
	protected $filesystem;

	public abstract function __construct(string $folder);
	public abstract function baseFolder(): string;
	public abstract function filesystem(): Filesystem;

	public function locale()
	{
		if ( ! is_null($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$locale = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}
		else if ( ! is_null($_SERVER['LANG']))
		{
			$locale = $_SERVER['LANG'];
		}
		else
		{
			$locale = "en_US";
		}
		if ($dot = strpos($locale, '.'))
		{
			$locale = substr($locale, $dot - 1);
		}
		return $locale;
	}
}