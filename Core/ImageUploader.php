<?php

namespace Core;

class ImageUploader{
    private $location;
    private $allowedTypes=array(
        'image/jpeg',
        'image/png',
        'image/gif');
    private $width;
    private $height;

    function __construct($location,$width,$height)
    {
        $this->location = $location;
        $this->height = $height;
        $this->width = $width;
    }

    public function upload(){

        if ( !empty( $_FILES ) ) {

            if(is_numeric($type = array_search($_FILES['file']['type'],$this->allowedTypes))) {

                switch ($type) {
                    case 0:
                        $image = imagecreatefromjpeg($_FILES['file']['tmp_name']);
                        break;
                    case 1:
                        $image = imagecreatefrompng($_FILES['file']['tmp_name']);
                        break;
                    default :
                        $image = imagecreatefromgif($_FILES['file']['tmp_name']);
                        break;
                }

                list($width,$height) = getimagesize($_FILES['file']['tmp_name']);

                $widthgap =0;
                $heightgap=0;
                if($width>=$height){
                    $nisbet = $this->width/$width;
                    $heightgap = ($this->height-$height*$nisbet)/2;
                }
                else{
                    $nisbet = $this->height/$height;
                    $widthgap = ($this->width-$width*$nisbet)/2;
                }
                $image2 = imagecreatetruecolor($this->width,$this->height);

                $reng = imagecolorallocate($image2,249,249,249);
                imagefill($image2,5,5,$reng);

                imagecopyresized($image2,$image,$widthgap,$heightgap,0,0,$width*$nisbet,$height*$nisbet,$width,$height);

                $fileName = rand(111,999).rand(111,999).rand(111,999).rand(111,999).".jpg";
                imagejpeg($image2,$this->location."/$fileName",90);

                imagedestroy($image);
                imagedestroy($image2);

                return $fileName;
            }
            else echo"olmaz";



        } else {

            echo 'No files';

        }
    }
}
