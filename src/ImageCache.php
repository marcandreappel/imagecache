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

use Intervention\Image\ImageManager;
use InvalidArgumentException;
use MarcAndreAppel\ImageCache\Exception\FileNotFound;
use MarcAndreAppel\Textr\Textr;


class ImageCache
{
	use Textr;

	protected $quality = 90;
	protected $width;
	protected $height;
	protected $method = null;
	protected $prefix = null;
	protected $enlarge = false;
	protected $image;
	protected $cache;
	protected $dirname;
	protected $basename;
	protected $extension;
	protected $filename;
	protected $hidden = true;

	/**
	 * ImageCache constructor.
	 *
	 * @param string $path
	 *
	 * @throws FileNotFound
	 */
	public function __construct(string $path)
	{
		if (!file_exists($path) || !is_file($path))
		{
			throw new FileNotFound("File not found in path: $path");
		}
		$info            = pathinfo($path);
		$this->dirname   = array_key_exists('dirname', $info) ? $info['dirname'] : null;
		$this->basename  = array_key_exists('basename', $info) ? $info['basename'] : null;
		$this->extension = array_key_exists('extension', $info) ? $info['extension'] : null;
		$this->filename  = array_key_exists('filename', $info) ? $info['filename'] : null;

		list($this->width, $this->height) = getimagesize($path);
	}

	/**
	 * @param int $width
	 * @param int $height
	 *
	 * @return ImageCache
	 */
	public function thumbnail(int $width, int $height): self
	{
		if (!$this->cache('thumbnail', $width, $height))
		{
			$scale    = max($width / $this->width(), $height / $this->height());
			$_width   = ceil($this->width() * $scale);
			$_height  = ceil($this->height() * $scale);
			$x_offset = floor(($_width - $width) / 2);
			$y_offset = floor(($_height - $height) / 2);

			$this->image->resize($_width, $_height);
			$this->image->crop($width, $height, $x_offset, $y_offset);

			$this->save();
		}
		return $this;
	}

	/**
	 * @param string   $method
	 * @param int      $width
	 * @param int|null $height
	 *
	 * @return bool
	 */
	private function cache(string $method, int $width = null, int $height = null): bool
	{
		if (!$this->enlarge)
		{
			$original_is_higher = false;
			$original_is_larger = false;
			if (is_null($height) || $height < $this->height)
			{
				$original_is_higher = true;
			}
			if (is_null($width) || $width < $this->width)
			{
				$original_is_larger = true;
			}
			if ( ! $original_is_higher || ! $original_is_larger)
			{
				$this->cache = $this->dirname;

				return true;
			}
		}
		if (!is_null($this->method))
		{
			$cache = "/{$this->urlify($this->method)}";
		}
		else
		{
			if ($method == 'scaled' && is_null($height))
			{
				$cache = "/$method/$width/auto";
			}
			else if ($method == 'scaled' && is_null($height))
			{
				$cache = "/$method/auto/$height";
			}
			else
			{
				$cache = "/$method/$width/$height";
			}
		}
		if (!is_null($this->prefix))
		{
			$cache = "/{$this->prefix}$cache";
		}

		if (!$this->hidden)
		{
			$this->cache = $this->dirname . $cache;
		}
		else
		{
			$this->cache = "{$this->dirname}/.cache$cache";
		}
		$cached = true;
		if (!is_dir($this->cache))
		{
			$umask = umask(0);
			mkdir($this->cache, 0755, true);
			umask($umask);
			$cached = false;
		}
		else if (!file_exists("{$this->cache}/{$this->basename}"))
		{
			$cached = false;
		}
		if (!$cached)
		{
			$image       = new ImageManager();
			$this->image = $image->make("{$this->dirname}/{$this->basename}");
		}
		return $cached;
	}

	/**
	 * @return int
	 */
	public function width(): int
	{
		return $this->image->getWidth();
	}

	/**
	 * @return int
	 */
	public function height(): int
	{
		return $this->image->getHeight();
	}

	/**
	 * @return ImageCache
	 */
	private function save()
	{
		$this->image->save("{$this->cache}/{$this->basename}", (int) $this->quality);
		clearstatcache();
		chmod("{$this->cache}/{$this->basename}", 0644);
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
		if (!$this->cache('cropped', $width, $height))
		{
			$this->image->crop($width, $height, $x_offset, $y_offset);
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
		if (!$this->cache('scaled', $width, $height))
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
				$this->image->widen($width);
				$this->save();

				return $this;
			}
			$this->image->heighten($height);
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
		if (!$this->cache('resized', $width, $height))
		{
			$this->image->resize($width, $height);
			$this->save();
		}

		return $this;
	}

	/**
	 * @param bool $override
	 *
	 * @return $this
	 */
	public function hidden(bool $override)
	{
		$this->hidden = $override;

		return $this;
	}

	/**
	 * @param string $override
	 *
	 * @return $this
	 */
	public function method(string $override)
	{
		$this->method = $this->urlify($override);

		return $this;
	}

	/**
	 * @param string $override
	 *
	 * @return $this
	 */
	public function prefix(string $override)
	{
		$this->prefix = $this->urlify($override);

		return $this;
	}

	/**
	 * @param int $override
	 *
	 * @return $this
	 */
	public function quality(int $override)
	{
		$this->quality = $override;

		return $this;
	}

	/**
	 * @param bool $override
	 *
	 * @return $this
	 */
	public function enlarge(bool $override)
	{
		$this->enlarge = $override;

		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return "{$this->cache}/{$this->basename}";
	}

	/**
	 * @param $name
	 *
	 * @return string
	 */
	public function __get($name)
	{
		return "{$this->cache}/{$this->basename}";
	}
}
