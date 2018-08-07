<?php
/**
 * ImageCache.php
 * @author      Marc-André Appel <marc-andre@appel.fun>
 * @copyright   2018 Marc-André Appel
 * @license     http://opensource.org/licenses/LGPL-3.0 LGPL-3.0
 * @link        https://github.com/marcandreappel/imagecache
 * @created     02/08/2018
 */

namespace MarcAndreAppel\ImageCache;

use \InvalidArgumentException;
use Intervention\Image\ImageManagerStatic;
use MarcAndreAppel\ImageCache\Adapter\Adapter;
use MarcAndreAppel\ImageCache\Exception\FileNotFound;
use MarcAndreAppel\Textr\Textr;

class ImageCache
{
	use Textr;

	public $quality = 90;
	public $sourceFile;
	public $width;
	public $height;

	protected $method = null;
	protected $baseFolder;
	protected $cacheFilename;
	protected $cacheFolder;
	protected $cachePath;

	private $adapter;
	private $filesystem;
	private $imageObject;

	/**
	 * ImageCache constructor.
	 *
	 * @param Adapter     $adapter
	 * @param string|null $sourceFile
	 */
	public function __construct(Adapter $adapter, string $sourceFile = null)
	{
		$this->adapter    = $adapter;
		$this->baseFolder = rtrim($adapter->baseFolder(), '/');
		$this->filesystem = $adapter->filesystem();

		if ( ! is_null($sourceFile))
		{
			try
			{
				$this->loadImage($sourceFile);
			}
			catch (\Exception $exception)
			{
				throw new $exception;
			}
		}
	}

	/**
	 * @param int $width
	 * @param int $height
	 *
	 * @return ImageCache
	 */
	public function thumbnail(int $width, int $height): self
	{
		if ( ! $this->prepareCache('thumbnail', $width, $height))
		{
			$scale           = max($width / $this->_width(), $height / $this->_height());
			$_width          = ceil($this->_width() * $scale);
			$_height         = ceil($this->_height() * $scale);
			$x_offset        = floor(($_width - $width) / 2);
			$y_offset        = floor(($_height - $height) / 2);

			$this->imageObject->resize($_width, $_height);
			$this->imageObject->crop($width, $height, $x_offset, $y_offset);

			$this->save();
		}
		return $this;
	}

	/**
	 * @param $width
	 * @param $height
	 *
	 * @return ImageCache
	 */
	public function resize($width, $height): self
	{
		if ( ! $this->prepareCache('resized', $width, $height))
		{
			$this->imageObject->resize($width, $height);
			$this->save();
		}
		return $this;
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @param int $x_offset
	 * @param int $y_offset
	 *
	 * @return ImageCache
	 */
	public function crop(int $width, int $height, int $x_offset, int $y_offset): self
	{
		if ( ! $this->prepareCache('cropped', $width, $height))
		{
			$this->imageObject->crop($width, $height, $x_offset, $y_offset);
			$this->save();
		}
		return $this;
	}

	/**
	 * @param int|null $width
	 * @param int|null $height
	 *
	 * @return ImageCache
	 */
	public function scale(int $width = null, int $height = null): self
	{
		if ( ! $this->prepareCache('scaled', $width, $height))
		{
			if (is_null($width) && is_null($height))
			{
				throw new InvalidArgumentException("At least one argument needs to be set");
			}
			if (!is_null($width) && !is_null($height))
			{
				$this->resize($width, $height);
				$this->save();

				return $this;
			}
			if (!is_null($width))
			{
				$this->imageObject->widen($width);
				$this->save();

				return $this;
			}
			$this->imageObject->heighten($height);
			$this->save();
		}
		return $this;
	}

	/**
	 * @param string $sourceFile
	 *
	 * @return ImageCache
	 * @throws FileNotFound
	 */
	public function loadImage(string $sourceFile): self
	{
		$_sourceFile = $sourceFile;
		if (strpos($_sourceFile, '/') !== 0)
		{
			$_sourceFile = "/$_sourceFile";
		}
		if ( ! is_file($_sourceFile))
		{
			$_sourceFile = $this->baseFolder . $_sourceFile;
		}
		if ( ! is_file($_sourceFile))
		{
			throw new FileNotFound("File not found");
		}
		$this->sourceFile    = $_sourceFile;
		$this->imageObject   = ImageManagerStatic::make($_sourceFile);
		$this->cacheFilename = $this->imageObject->basename;

		return $this;
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'publicPath':
				return $this->cacheFolder;
				break;
			case 'absolutePath':
				return $this->baseFolder.$this->cacheFolder;
				break;
			case 'absoluteImage':
			case 'absolute':
				return $this->cachePath;
				break;
			case 'publicImage':
			case 'public':
			default:
				return "{$this->cacheFolder}/{$this->cacheFilename}";
				break;
		}
	}

	/**
	 * @param string $override
	 *
	 * @return $this
	 */
	public function method(string $override)
	{
		$this->method = $override;

		return $this;
	}

	/**
	 * @param string   $method
	 * @param int      $width
	 * @param int|null $height
	 *
	 * @return bool
	 */
	private function prepareCache(string $method, int $width = null, int $height = null): bool
	{
		if ( ! is_null($this->method))
		{
			$handler = rtrim($this->method, '/');
			$cacheFolder = "/$handler";
		}
		else
		{
			if ($method == 'scale' && is_null($height))
			{
				$cacheFolder = "/$method/$width/auto";
			}
			else if ($method == 'scale' && is_null($height))
			{
				$cacheFolder = "/$method/auto/$height";
			}
			else
			{
				$cacheFolder = "/$method/$width/$height";
			}
		}

		$baseFolder = $this->adapter->baseFolder();
		if (strrpos($baseFolder, '/', -1))
		{
			$baseFolder = substr($baseFolder, 0, strlen($baseFolder)-1);
		}
		$this->cachePath = "$baseFolder$cacheFolder/{$this->imageObject->basename}";

		if ( ! $this->filesystem->has($cacheFolder))
		{
			$this->filesystem->createDir($cacheFolder);
			$hasCache = false;
		}
		else
		{
			$hasCache = true;
		}
		$this->cacheFolder = $cacheFolder;

		return $hasCache;
	}

	/**
	 * @return ImageCache
	 */
	private function save(): self
	{
		$this->imageObject->save($this->cachePath);
		/**
		 * @brief Clear the cached file size and refresh the image information
		 */
		clearstatcache();
		chmod($this->cachePath, 0644);

		return $this;
	}

	/**
	 * @return int
	 */
	public function width(): int
	{
		return $this->imageObject->getWidth();
	}

	/**
	 * @return int
	 */
	public function height(): int
	{
		return $this->imageObject->getHeight();
	}
}