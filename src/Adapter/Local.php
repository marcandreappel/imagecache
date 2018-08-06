<?php
/**
 * Local.php
 * @author      Marc-André Appel <marc-andre@appel.fun>
 * @copyright   2018 Marc-André Appel
 * @license     http://opensource.org/licenses/LGPL-3.0 LGPL-3.0
 * @link        https://github.com/marcandreappel/imagecache
 * @created     03/08/2018
 */

namespace MarcAndreAppel\ImageCache\Adapter;

use League\Flysystem\Adapter\Local as FilesystemAdapter;
use League\Flysystem\Filesystem;

class Local extends Adapter
{
	/**
	 * Loading the root of the image folder
	 *
	 * @param string $baseFolder
	 */
	public function __construct(string $baseFolder)
	{
		$adapter          = new FilesystemAdapter($baseFolder);
		$this->filesystem = new Filesystem($adapter);
	}

	public function baseFolder(): string
	{
		return $this->filesystem->getAdapter()->getPathPrefix();
	}

	public function filesystem(): Filesystem
	{
		return $this->filesystem;
	}
}