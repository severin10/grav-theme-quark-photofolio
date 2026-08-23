<?php
namespace Grav\Theme;

use Grav\Common\Filesystem\Folder;
use Grav\Common\Media\Interfaces\MediaObjectInterface;
use Grav\Common\Theme;

class QuarkPhotofolio extends Theme
{
    public static function getSubscribedEvents()
    {
        return [
            'onThemeInitialized'  => ['onThemeInitialized', 0],
            'onTwigLoader'        => ['onTwigLoader', 0],
            'onTwigInitialized'   => ['onTwigInitialized', 0],
        ];
    }

    public function onThemeInitialized()
    {
    }

    public function onTwigLoader()
    {
        $locator = $this->grav['locator'];
        foreach ((array) $locator->findResources('theme://images') as $path) {
            $this->grav['twig']->addPath($path, 'images');
        }
    }

    public function onTwigInitialized()
    {
        $twig = $this->grav['twig'];

        $twig->twig->addFunction(
            new \Twig\TwigFunction('tile_watermark', [$this, 'tileWatermark'])
        );

        // Grav's form plugin renders submit/reset buttons with a plain
        // "button" class unless a theme names one of its own. .btn is
        // already defined (inherited from Quark 2's theme.css) but nothing
        // used it, so form buttons rendered with no real styling.
        $twig->twig_vars['form_button_classes'] = 'btn';
    }

    /**
     * Tiles a watermark image across a full-resolution photo, switching
     * between a white and a black rendition of it per tile position based
     * on the photo's local brightness there — instead of a single fixed
     * color, which disappears against a same-toned background. Both
     * renditions are composited with plain alpha-over (GD's `imagecopy()`
     * with alpha blending, the standard, well-tested way to do this — no
     * hand-rolled blend-mode math).
     *
     * The derivative is written to its own cache file, self-contained in
     * this theme (not routed through Grav's built-in Medium::watermark() /
     * ImageMedium caching, whose object-based operation hashing did not
     * reliably change when only the watermark file's bytes changed under
     * the same filename — see tileWatermarkCacheFile()). Bypassing that
     * also sidesteps Medium::merge(array $data), a header-merging method
     * inherited from Grav\Common\Data\Data that shadows the fluent
     * image-compositing merge() a Medium would otherwise expose only via
     * its magic __call() proxy.
     *
     * @param MediaObjectInterface $medium The full-resolution photo.
     * @param string $watermarkPath Stream path to the watermark image, e.g. 'theme://images/watermark/logo.png'.
     * @param float $tileWidthPercent Watermark tile width, as % of the photo's width.
     * @param float $spacingPercent Gap between tiles, as % of the photo's width.
     * @param float $transparencyPercent Extra transparency on top of the watermark's own alpha, 0 (unchanged) to 100 (fully invisible).
     * @return string Public URL of the watermarked derivative, or the plain photo URL if watermarking isn't possible.
     */
    public function tileWatermark($medium, string $watermarkPath, float $tileWidthPercent = 10, float $spacingPercent = 8, float $transparencyPercent = 0): string
    {
        if (!$medium) {
            return '';
        }

        $locator = $this->grav['locator'];
        $watermarkFile = $locator->findResource($watermarkPath);
        $photoFile = $medium->get('filepath');

        if ($tileWidthPercent <= 0 || $watermarkFile === false || !is_file($watermarkFile) || !$photoFile || !is_file($photoFile)) {
            return $medium->url();
        }

        [$cacheFile, $publicUrl] = $this->tileWatermarkCacheFile($photoFile, $watermarkFile, $tileWidthPercent, $spacingPercent, $transparencyPercent);

        if (!is_file($cacheFile)) {
            $this->renderTiledWatermark($photoFile, $watermarkFile, $cacheFile, $tileWidthPercent, $spacingPercent, $transparencyPercent);
        }

        return is_file($cacheFile) ? $publicUrl : $medium->url();
    }

    /**
     * Builds a cache path/URL whose filename is a hash of everything that
     * can change the output: both source files' content (mtime + size,
     * cheap stand-ins for a full checksum) and the tiling parameters. This
     * guarantees a fresh, distinct URL whenever any of those change — most
     * importantly, replacing the watermark upload with a different image
     * under the *same* filename still produces a new URL, so browsers and
     * any CDN in front of the site fetch the new version instead of
     * indefinitely serving what they already cached for the old one.
     *
     * @return array{0: string, 1: string} [absolute filesystem path, public URL]
     */
    private function tileWatermarkCacheFile(string $photoFile, string $watermarkFile, float $tileWidthPercent, float $spacingPercent, float $transparencyPercent): array
    {
        $locator = $this->grav['locator'];

        $key = md5(implode('|', [
            $photoFile, (string) filemtime($photoFile), (string) filesize($photoFile),
            $watermarkFile, (string) filemtime($watermarkFile), (string) filesize($watermarkFile),
            $tileWidthPercent, $spacingPercent, $transparencyPercent,
        ]));

        $ext = strtolower(pathinfo($photoFile, PATHINFO_EXTENSION));
        $ext = $ext === 'jpeg' ? 'jpg' : ($ext ?: 'jpg');

        $cacheDir = $locator->findResource('cache://images/photofolio-watermarked', true, true);
        $cacheFile = $cacheDir . '/' . $key . '.' . $ext;

        $imagesDir = trim((string) $locator->findResource('cache://images', false), '/');
        $publicUrl = rtrim((string) $this->grav['base_url'], '/') . '/' . $imagesDir . '/photofolio-watermarked/' . $key . '.' . $ext;

        return [$cacheFile, $publicUrl];
    }

    private function renderTiledWatermark(string $photoFile, string $watermarkFile, string $outFile, float $tileWidthPercent, float $spacingPercent, float $transparencyPercent = 0): void
    {
        $photo = $this->loadGdImage($photoFile);
        $watermark = $this->loadGdImage($watermarkFile);
        if (!$photo || !$watermark) {
            return;
        }

        $photoWidth = imagesx($photo);
        $photoHeight = imagesy($photo);

        $tileWidth = max(1, (int) round($photoWidth * $tileWidthPercent / 100));
        $tileHeight = max(1, (int) round($tileWidth * imagesy($watermark) / imagesx($watermark)));

        // 0-127 additional alpha pushed onto every pixel (see recolorTile),
        // on top of whatever alpha the watermark image already has.
        $alphaOffset = (int) round(max(0, min(100, $transparencyPercent)) / 100 * 127);

        // Two solid-color renditions of the uploaded mark, alpha (shape +
        // opacity) untouched apart from $alphaOffset — only their RGB
        // differs. Per tile placement we pick whichever contrasts with the
        // photo there, so the watermark's own color (often black) never
        // determines whether it's visible.
        $whiteTile = $this->recolorTile($watermark, $tileWidth, $tileHeight, true, $alphaOffset);
        $blackTile = $this->recolorTile($watermark, $tileWidth, $tileHeight, false, $alphaOffset);

        $spacing = max(0, (int) round($photoWidth * $spacingPercent / 100));
        $stepX = $tileWidth + $spacing;
        $stepY = $tileHeight + $spacing;

        imagealphablending($photo, true);

        for ($ty = 0; $ty < $photoHeight; $ty += $stepY) {
            for ($tx = 0; $tx < $photoWidth; $tx += $stepX) {
                $w = min($tileWidth, $photoWidth - $tx);
                $h = min($tileHeight, $photoHeight - $ty);
                $tile = $this->averageBrightness($photo, $tx, $ty, $w, $h) > 128 ? $blackTile : $whiteTile;
                imagecopy($photo, $tile, $tx, $ty, 0, 0, $w, $h);
            }
        }

        Folder::create(dirname($outFile));
        $quality = (int) $this->grav['config']->get('system.images.default_image_quality', 80);
        $ext = strtolower((string) pathinfo($outFile, PATHINFO_EXTENSION));

        if ($ext === 'png') {
            imagepng($photo, $outFile);
        } elseif ($ext === 'webp' && function_exists('imagewebp')) {
            imagewebp($photo, $outFile, $quality);
        } else {
            imagejpeg($photo, $outFile, $quality);
        }

        imagedestroy($photo);
        imagedestroy($watermark);
        imagedestroy($whiteTile);
        imagedestroy($blackTile);
    }

    /**
     * @return \GdImage|null
     */
    private function loadGdImage(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) {
            return null;
        }

        $image = match ($info['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            'image/gif'  => @imagecreatefromgif($path),
            default      => false,
        };

        if (!$image) {
            return null;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * Resizes the watermark to $tileWidth x $tileHeight and forces its RGB
     * to a solid color while leaving its alpha channel (shape + opacity)
     * untouched, via GD's built-in IMG_FILTER_COLORIZE — an offset of ±255
     * pushes every channel to its clamp, i.e. pure white or pure black,
     * regardless of the source pixel's original color. $alphaOffset (0-127)
     * uses the same filter call's alpha argument to additionally fade the
     * whole tile toward transparent, on top of its own per-pixel alpha.
     *
     * @return \GdImage
     */
    private function recolorTile($watermark, int $tileWidth, int $tileHeight, bool $white, int $alphaOffset = 0)
    {
        $tile = imagecreatetruecolor($tileWidth, $tileHeight);
        imagealphablending($tile, false);
        imagesavealpha($tile, true);
        imagecopyresampled($tile, $watermark, 0, 0, 0, 0, $tileWidth, $tileHeight, imagesx($watermark), imagesy($watermark));

        $offset = $white ? 255 : -255;
        imagefilter($tile, IMG_FILTER_COLORIZE, $offset, $offset, $offset, $alphaOffset);

        return $tile;
    }

    /**
     * Average perceptual brightness (ITU-R BT.601 luma) of a region, sampled
     * on a coarse grid rather than every pixel — plenty accurate for a
     * "lighter or darker" choice and cheap even on a large photo.
     */
    private function averageBrightness($photo, int $x0, int $y0, int $width, int $height): float
    {
        $step = max(1, (int) (min($width, $height) / 8));
        $sum = 0;
        $count = 0;

        for ($y = 0; $y < $height; $y += $step) {
            for ($x = 0; $x < $width; $x += $step) {
                $rgb = imagecolorat($photo, $x0 + $x, $y0 + $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $sum += 0.299 * $r + 0.587 * $g + 0.114 * $b;
                $count++;
            }
        }

        return $count > 0 ? $sum / $count : 128.0;
    }
}
