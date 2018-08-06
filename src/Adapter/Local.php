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
	public function __construct(string $folder)
	{
		$adapter          = new FilesystemAdapter($folder);
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