<?php
/*
   Class: image
   A class that helps with common imaging
*/
class Gorilla3D_Image
{
    /*
        Function: scale

        Scale an image based on a max width and height

        (start code)
        $image = imagecreatefrompng('sample.png');

        // Save a propotionally scaled jpg image no wider then 320px and no higher then 240px
        image::scale($image, 320, 240, 'sample.320.jpg');

        // Same as above but the height does not matter.
        image::scale($image, 320, null, 'sample.320.jpg');

        // Change the file name to .png to save as png and save transparency
        image::scale($image, 320, null, 'sample.320.png');

        // If no filename is give it gives back the image resource
        $image_scaled = image::scale($image, 320);
        imagejpeg($image_scaled, 'sample.320.jpg', 85);
        (end)

        Parameters:

          image - image resource identifier
          maxwidth - maximum width to scale an image
          maxheight - maximum height to scale an image
          filename - filename of the image to save to

        Returns:

          true | image resource identifier
    */
    static function scale(&$image, $maxwidth=null, $maxheight=null, $filename=null)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        if ($maxwidth) {
            $newwidth  = $maxwidth;
            $newheight = $height * $newwidth / $width;
        } else if($maxheight) {
            $newheight = $maxheight;
            $newwidth  = $width * $newheight / $height;
        } else {
            throw new Exception('You never picked a width or height');
        }
        if ($maxwidth and $maxheight and $maxheight < $newheight) {
            $newheight =& $maxheight;
            $newwidth  = $width * $newheight / $height;
        }
        if($maxwidth and $maxheight and $maxwidth < $newwidth) {
            $newwidth  =& $maxwidth;
            $newheight = $height * $newwidth / $width;
        }
        $resampled = imagecreatetruecolor($newwidth, $newheight);
        $extension =  self::extension($filename);
        if ($extension == 'png') {
            self::transparency($resampled);
        }
        imagecopyresampled($resampled, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        return self::save($resampled, $filename);
    }

    /*
        Function: extension

        Grab the file extension and lowercase it

        (start code)
        $filname = 'myimage.png';
        $ext = image::extension($filname);
        echo $ext;
        // outputs: 'png'
        (end)

        Parameters:

          filename - filename of the image

        Returns:
     
          string
    */
    static function extension($filename) {
     	$imagearray = explode("." , $filename);
    	$extension =  strtolower(array_pop($imagearray));
    	return $extension;
    }
    
    static function transparency(&$image) {
        imagealphablending($resampled, false);
        $color = imagecolorallocatealpha($resampled, 0, 0, 0, 127);
        imagefill($resampled, 0, 0, $color);
        imagesavealpha($resampled, true);
    }

    static function load($filename) {
        $image = @imagecreatefromjpeg($filename);
        if (!$image) {
            $image = @imagecreatefrompng($filename);
        }
        if (!$image) {
            $image = @imagecreatefromgif($filename);
        }
        return $image;
    }

    static function save($image, $filename=null)
    {
        if ($filename == null) {
            return $image;
        }
        $extension =  self::extension($filename);
        if ($extension == 'png') {
            imagepng($image, $filename, 9);
        } else {
            imagejpeg($image, $filename, 85);
        }
        return true;        
    }
    
    static function isLoadable($filename)
    {
        $pixel_max = 4;
        //-- If they upload a PNG then assume its a alpha mapped 6 million colors
        if (self::extension($filename) === 'png') {
            $pixel_max = 5;
        }
    
        list($width, $height, $type, $attr) = getimagesize($filename);
        $image_memory = ($width * $height * $pixel_max);
        $current_memory = memory_get_usage(true);
        $needed_memory = ($image_memory + $current_memory) / 1024 / 1024;
        $max_memory = str_replace('M', '', ini_get('memory_limit'));
        $process_image = true;
        if ($max_memory and $needed_memory > $max_memory) {
            $process_image = false;
        }
        return $process_image;
    }

    /*
        Function: square_crop

        Scale an image based on a max width and height

        (start code)
        $image = imagecreatefrompng('sample.png');

        // Save a sqaure jpg image 100px by 100px
        image::square_crop($image, 100, 'sample.320.jpg');

        // Change the file name to .png to save as png and save transparency
        image::square_crop($image, 100, 'sample.320.png');

        // If no filename is give it gives back the image resource
        $image_scaled = image::scale($image, 100);
        imagejpeg($image_scaled, 'sample.100.jpg', 85);
        (end)

        Parameters:

          image - image resource identifier
          maxwidth - maximum width to scale an image
          maxheight - maximum height to scale an image
          filename - filename of the image to save to

        Returns:

          true | image resource identifier
    */    
    static function squareCrop($image, $scale, $filename)
    {
    	$width = imagesx($image);    
    	$height = imagesy($image);    
    	$ratiox = $width / $height * $scale;    
    	$ratioy = $height / $width * $scale;    
    	$newheight = ($width <= $height) ? $ratioy : $scale;    
    	$newwidth = ($width <= $height) ? $scale : $ratiox;    
    	$cropx = ($newwidth - $scale != 0) ? ($newwidth - $scale) / 2 : 0;    
    	$cropy = ($newheight - $scale != 0) ? ($newheight - $scale) / 2 : 0;    
    	$resampled = imagecreatetruecolor($newwidth, $newheight);    
    	$cropped = imagecreatetruecolor($scale, $scale);    
    	$valid = imagecopyresampled($resampled, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);   
    	if (!$valid) {
    	    unset($cropped);
    	    unset($resampled);
    	    return false;
    	}  
    	$valid = imagecopy($cropped, $resampled, 0, 0, $cropx, $cropy, $newwidth, $newheight);
    	unset($resampled);
    	if (!$valid) {
    	    unset($cropped);
    	    unset($resampled);
    	    return false;
    	} 
        return self::save($cropped, $filename);
	}
}
?>
