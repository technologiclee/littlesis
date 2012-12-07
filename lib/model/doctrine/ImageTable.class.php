<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ImageTable extends Doctrine_Table
{
  static function generateFilename($originalFilename='')
  {
    return sha1(md5($originalFilename . date('U') . rand(1000, 9999))) . '_' . date('U') . '.png';
  }

  static function generateS3path($type, $filename)
  {
    return sfConfig::get('app_amazon_s3_folder') . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $filename;
  }

  static function generateS3Url($source)
  {
    return (strpos('/sf', $source) === 0) ? $source :
      sfConfig::get('app_amazon_s3_base') . DIRECTORY_SEPARATOR .
      sfConfig::get('app_amazon_s3_bucket') . 
      (strpos($source, '/sf/') === false ? DIRECTORY_SEPARATOR . sfConfig::get('app_amazon_s3_folder') . DIRECTORY_SEPARATOR : '' ) .
      $source;
  }
  
  static function createFile($filename, $readPath, $writePath, $maxWidth=null, $maxHeight=null)
  {
    if (!$size = getimagesize($readPath))
    {
      return false;
    }   

    if ($maxWidth && $size[0] <= $maxWidth)
    {
      $maxWidth = null;
    }

    if ($maxHeight && $size[1] <= $maxHeight)
    {
      $maxHeight = null;
    }

    
    $thumbnail = new LsThumbnail($maxWidth, $maxHeight, true, true, 100);
    $thumbnail->loadFile($readPath);
    $savePath = sfConfig::get('sf_image_dir') . DIRECTORY_SEPARATOR . $writePath . DIRECTORY_SEPARATOR . $filename; 
    $thumbnail->save($savePath, 'image/png');
    
    # if s3 enabled, save to s3
    if (sfConfig::get('app_amazon_enable_s3'))
    {
      $s3 = new S3(sfConfig::get('app_amazon_access_key'), sfConfig::get('app_amazon_secret_key'));
      $input = $s3->inputResource($f = fopen($savePath, "rb"), $s = filesize($savePath));
      $uri = self::generateS3path($writePath, $filename);

      if (!S3::putObject($input, sfConfig::get('app_amazon_s3_bucket'), $uri, S3::ACL_PUBLIC_READ)) 
      {
        return false;
      }
    }
    
    return $thumbnail;
  }

  static function createFiles($filePath, $originalFilename='')
  {
    //generate filename
    $filename = self::generateFilename($originalFilename);
    $originalFilePath = $filePath;
    //if remote path, create temporary local copy
    if (preg_match('#http(s)?://#i', $filePath))
    {
      $url = $filePath;

      $defaultHeaders = array(
        'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20061205 Iceweasel/2.0.0.1 (Debian-2.0.0.1+dfsg-2)'
      );
      
      $b = new sfWebBrowser($defaultHeaders, 'sfCurlAdapter', array('cookies' => true));

      if ($b->get($url)->responseIsError())
      {
        return false;
      }

      $fileData = $b->getResponseText();
      unset($b);

      $filePath = sfConfig::get('sf_temp_dir') . DIRECTORY_SEPARATOR . $filename;
      
      $localImage = fopen($filePath, 'wb');
      fwrite($localImage, $fileData);

      if (!fclose($localImage))
      {
        throw new Exception("Couldn't close file: " . $filename);
      }
      
      if (strtolower(substr($originalFilePath, -3, 3)) == "svg" )
      {
        $convert = trim(shell_exec('which convert'));
        
        if ($convert)
        {
          $svgFilepath = sfConfig::get('sf_temp_dir') . DIRECTORY_SEPARATOR . substr($filename, 0 -3) . ".svg";
          $pngFilepath = sfConfig::get('sf_temp_dir') . DIRECTORY_SEPARATOR . $filename;
  
          rename($pngFilepath, $svgFilepath);        
          exec("$convert $svgFilepath $pngFilepath");
        }
      }
      
    }

    //create full size file up to 1024x1024
    if (!self::createFile($filename, $filePath, 'large', 1024, 1024))
    {
      return false;
    }
      
    //create profile size
    if (!self::createFile($filename, $filePath, 'profile', 200, 200))
    {
      unlink(sfConfig::get('sf_image_dir') . DIRECTORY_SEPARATOR . 'large' . DIRECTORY_SEPARATOR . $filename);
      return false;
    }

    //create small size
    if (!self::createFile($filename, $filePath, 'small', null, 50))
    {
      unlink(sfConfig::get('sf_image_dir') . DIRECTORY_SEPARATOR . 'large' . DIRECTORY_SEPARATOR . $filename);
      unlink(sfConfig::get('sf_image_dir') . DIRECTORY_SEPARATOR . 'profile' . DIRECTORY_SEPARATOR . $filename);
      return false;
    }    
    
    //remove temporary file
    if (isset($url))
    {
      unlink($filePath);
    }

    return $filename;
  }
  
  
  static function getPath($image, $type='profile')
  {
    if (is_string($image) || !$filename = @$image['filename'])
    {
      $filename = $image;
    }

    return $type . DIRECTORY_SEPARATOR . $filename;
  }
  
  
  static function getInternalUrl($image)
  {
    if (!$id = $image['id'])
    {
      throw new Exception("Can't get internal URL for new image");
    }
    
    return 'entity/image?id=' . $id;  
  }
}