<?php

/**
 * Local Image Handler
 *
 * @package     LalaTaxi
 * @subpackage  Services
 * @category    Image Handler
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
*/

namespace App\Services;

use App\Contracts\ImageHandlerInterface;
use File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class LocalImageHandler implements ImageHandlerInterface
{

    public $imageManager;

    public function __construct()
    {
        // create an image manager instance with favored driver
        $this->imageManager = new ImageManager(new Driver());
    }
    /**
     * Validate Extension is valid or not
     *
     * @param String $[ext] [extension]
     * @param Array $[addional_ext] [additional extensions]
     * @return Boolean
     */
    protected function validateExtension($ext, $addional_ext = [])
    {
        $ext = strtolower($ext);
        $valid_extensions = array_merge(['png', 'jpg', 'jpeg', 'gif', 'webp'], $addional_ext);
        return in_array($ext, $valid_extensions);
    }

    /**
     * Compress Given image to given size
     *
     * @param String $[source_url] [source_url]
     * @param String $[destination_url] [destination_url]
     * @param Int $[quality] [quality]
     * @return String Image URL
     */
    protected function compress_image($source_url, $destination_url, $quality, $width = 225, $height = 225)
    {
        $info = getimagesize($source_url);
        if (!$info) {
            return false;
        }

        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source_url);
            $exif = @exif_read_data($source_url);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source_url);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source_url);
        } elseif ($info['mime'] == 'image/webp') {
            $image = imagecreatefromwebp($source_url);
        }

        if (isset($exif) && !empty($exif['Orientation'])) {
            $imageResource = imagecreatefromjpeg($source_url);
            switch ($exif['Orientation']) {
                case 3:
                    $image = imagerotate($imageResource, 180, 0);
                    break;
                case 6:
                    $image = imagerotate($imageResource, -90, 0);
                    break;
                case 8:
                    $image = imagerotate($imageResource, 90, 0);
                    break;
                default:
                    $image = $imageResource;
            }
        }

        imagejpeg($image, $destination_url, $quality);
        $this->crop_image($source_url, $width, $height);
        return $destination_url;
    }

    /**
     * Crop Given image to given size
     *
     * @param String $[source_url] [source_url]
     * @param Int $[crop_width] [crop_width]
     * @param Int $[crop_height] [crop_height]
     * @return String Image URL
     */
    protected function crop_image($source_url = '', $crop_width = 225, $crop_height = 225, $destination_url = '')
    {
        ini_set('memory_limit', '-1');
        $image =  $this->imageManager->read($source_url);
        $image_width = $image->width();
        $image_height = $image->height();

        if ($image_width < $crop_width && $crop_width < $crop_height) {
            $image = $image->resize($crop_width, $image_height);
        }
        if ($image_height < $crop_height  && $crop_width > $crop_height) {
            $image = $image->resize($crop_width, $crop_height);
        }

        $primary_cropped_image = $image;

        $croped_image = $primary_cropped_image->resize($crop_width, $crop_height);

        if ($destination_url == '') {
            $source_url_details = pathinfo($source_url);
            $destination_url = @$source_url_details['dirname'] . '/' . @$source_url_details['filename'] . '_' . $crop_width . 'x' . $crop_height . '.' . @$source_url_details['extension'];
        }

        $croped_image->save($destination_url);
        return $destination_url;
    }

    /**
     * Upload image to storage
     *
     * @param UploadedFile $[image]
     * @param Array $[options] [options related to image upload]
     * @return Array Image data
     */
    public function upload($image, $options = [])
    {
        $ext = $image->getClientOriginalExtension();
        $addional_ext = $options['extensions'] ?? [];
        $valid = $this->validateExtension($ext, $addional_ext);
        if (!$valid) {
            return [
                'status' => false,
                'status_message' => 'Invalid File Type',
            ];
        }

        if (isset($options['dir_name'])) {
            $dirname = $options['dir_name'];
        } else {
            $dirname = dirname($_SERVER['SCRIPT_FILENAME']);
        }

        $dir_name = $dirname . $options['target_dir'];

        if (isset($options['file_name'])) {
            $file_name = $options['file_name'];
        } else {
            $file_name = 'image-' . time() . '.' . $ext;
        }

        try {
            // Recreate missing directories and repair loose permissions so a
            // container that lost ownership of public/images still accepts
            // uploads instead of raising a fatal warning (HTTP 500).
            if (!is_dir($dir_name)) {
                @mkdir($dir_name, 0775, true);
            }
            if (is_dir($dir_name) && !is_writable($dir_name)) {
                @chmod($dir_name, 0775);
            }

            if (!is_dir($dir_name) || !is_writable($dir_name)) {
                return [
                    'status' => false,
                    'status_message' => 'Unable to Upload. Permission Denied.',
                ];
            }

            if (!$image->move($dir_name, $file_name)) {
                return [
                    'status' => false,
                    'status_message' => 'Failed To Upload Image',
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Image upload failed', [
                'dir' => $dir_name,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'status_message' => 'Unable to Upload. Permission Denied.',
            ];
        }


        if (isset($options['compress_size'])) {
            foreach ($options['compress_size'] as $size) {
                $this->compress_image($dir_name . "/" . $file_name, $dir_name . "/" . $file_name, 80, $size['height'], $size['width']);
            }
        }

        $return_data['status'] = true;
        $return_data['file_name'] = $file_name;
        return $return_data;
    }

    /**
     * Delete image from storage
     *
     * @param String $[image]
     * @return Boolean
     */
    public function delete($image, $options = [])
    {
        try {
            $photo_details = pathinfo($image);
            $target_file = $options['file_path'] . $photo_details['filename'] . '.' . $photo_details['extension'];
            $file_path = public_path($target_file);
            if (file_exists($file_path)) {
                File::delete($file_path);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Fetch image from storage
     *
     * @param String $[file_name]
     * @param Array $[options] [options related to image upload]
     * @return String Image path
     */
    public function getImage($file_name, $options = [])
    {
        try {
            $photo_details = pathinfo($file_name);
            $target_file = $options['file_path'] . $photo_details['filename'] . $options['name_suffix'] . '.' . $photo_details['extension'];
            $file_path = public_path($target_file);
            if (file_exists($file_path)) {
                return asset($target_file);
            }
            return url('image/default.png');
        } catch (\Exception $e) {
            return url('image/default.png');
        }
    }
}
