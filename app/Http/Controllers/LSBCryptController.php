<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FFMpeg;

class LSBCryptController extends Controller
{
    public function encodeLSBCrypt()
    {
        return view('lsb_crypt');
    }

    public function LSBEncodeCrypt(Request $request)
    {
        return $request->all();
        $validator = Validator::make($request->all(), [
            'msg' => 'required',
            'password' => 'required',
            'encImage' => 'required'
        ]);
        if($validator->passes())
        {   
            // dd($request->all());   
            $pictures = $request->encImage;
            // $pictures = base64_encode($pictures);
            $pictures = base64_encode(file_get_contents($request->file('encImage')));
            // dd($image);

            // dd($pictures);
            $pictures = str_replace('data:image/png;base64,', '', $pictures);
            $pictures = str_replace(' ', '+', $pictures);
            // dd($pictures);
            // $imageName = str_random(10).'.'.'png';
            // $imagePath = public_path(). '/' . $imageName;
            // \File::put($imagePath , base64_decode($pictures));
            // return response($pictures);
            // dd($pictures);
            $original = preg_replace('/data:image\/\w+;base64,/', '', $pictures);
            $original = base64_decode($original);
            $imageOriginal = imagecreatefromstring($original);
            $x_dimension = imagesx($imageOriginal); //height
            $y_dimension = imagesy($imageOriginal); //width
            // $original = base64_encode($pictures);
            // dd($original);
            // dd($imageOriginal);
            // dd($);
            // dd($y_dimension);
            
            $key = $request->password;
            $imageCrypto = $imageOriginal;
            $string =  $request->msg;
            // return response($imageCrypto);
            $stringCount = strlen($string);
            
            $iv = "1234567812345678";
            
            $stringCrypto = openssl_encrypt($string, 'AES-256-CFB', $key, OPENSSL_RAW_DATA, $iv);
            $bin = $this->textBinASCII2($stringCrypto); //string to array
            
            $stringLength = $this->textBinASCII2((string)strlen($bin));
            //$unbinStringLength = (int)$this->stringBinToStringChars8($stringLength);
            
            //$cryptoString = $this->stringBinToStringChars8($bin);
            //$output = openssl_decrypt($cryptoString, 'AES-256-CFB', $key, OPENSSL_RAW_DATA, $iv);
            
            $sign = $this->textBinASCII2('uzairsaeed');
            //$unbinSign = $this->stringBinToStringChars8($sign);
            
            $binaryText = str_split($stringLength.$sign.$bin);
            $textCount = count($binaryText);
            $count = 0;
            
            
            for ($x = 0; $x < $x_dimension; $x++) {

                if ($count >= $textCount)
                    break;

                for ($y = 0; $y < $y_dimension; $y++) {

                    if ($count >= $textCount)
                        break;  

                    $rgbOriginal = imagecolorat($imageOriginal, $x, $y);
                    $r = ($rgbOriginal >> 16) & 0xFF;
                    $g = ($rgbOriginal >> 8) & 0xFF;
                    $b = $rgbOriginal & 0xFF;
                    
                    $blueBinaryArray = str_split((string)base_convert($b,10,2));
                    $blueBinaryArray[count($blueBinaryArray)-1] = $binaryText[$count];
                    $blueBinary = implode($blueBinaryArray);
                    
                    $color = imagecolorallocate($imageOriginal, $r, $g,
                    bindec($blueBinary));
                    imagesetpixel($imageCrypto, $x, $y, $color);
                    
                    $count++;
                }
            }
            //$imageSave = imagepng($imageCrypto,'C:\Users\User\Desktop\aes\aes-'.$stringCount.'.png');
            ob_start();
            imagepng($imageCrypto);
            $image_string = base64_encode(ob_get_contents());
            ob_end_clean();
            // $base64 = $image_string;
            $base64 = $image_string;
            imagedestroy($imageCrypto);

            // $base64 = base64_decode($base64);
            $base64 = base64_decode($base64);
            // dd($base64);
            
            $imageName = str_random(10).uniqid().'.'.'png';
            $imagePath = public_path().'/'. $imageName;
            \File::put($imagePath , $base64);

            // dd($imagePath);
            
            return response()->json(['encImage' => $imagePath]);
        }
        else
        {
            return response()->json($validator->messages(), 200);
        }
    }

    public function LSBDecodeCrypt(Request $request)
    {
        $pictures = $request->get('pictures');
        $original = preg_replace('/data:image\/\w+;base64,/', '', $pictures['original']);
        $original = base64_decode($original);
        $imageOriginal = imagecreatefromstring($original);

        $x_dimension = imagesx($imageOriginal); //height
        $y_dimension = imagesy($imageOriginal); //width

        $binaryString = '';

        for ($x = 0; $x < $x_dimension; $x++) {

            for ($y = 0; $y < $y_dimension; $y++) {

                $rgbOriginal = imagecolorat($imageOriginal, $x, $y);

                $b = $rgbOriginal & 0xFF;

                $blueBinaryArray = str_split((string)base_convert($b, 10, 2));
                $bit = $blueBinaryArray[count($blueBinaryArray) - 1];
                $binaryString .= $bit;
            }
        }

        $iv = "1234567812345678";
        $key = $request->get('password');

        $sign = $this->textBinASCII2('gravitation');
        $lengthSign = strlen($sign);
        $position = strpos($binaryString, $sign);
        $lengthBinData = mb_substr($binaryString, 0, $position);
        $lengthData = $this->stringBinToStringChars8($lengthBinData);
        $positionData = $position + $lengthSign;
        $binaryData = mb_substr($binaryString, $positionData, $lengthData);

        $cryptoString = $this->stringBinToStringChars8($binaryData);
        $output = openssl_decrypt($cryptoString, 'AES-256-CFB', $key, OPENSSL_RAW_DATA, $iv);

        return response()->json(['text' => $output]);
    }

    public function stringBinToStringChars8($strBin)
    {
        $arrayChars = str_split($strBin, 8);
        $result = '';
        for ($i = 0; $i<count($arrayChars); $i++)
        {
            $result.=$this->ASCIIBinText2($arrayChars[$i]);
        }
        return $result;
    }

    function textBinASCII2($text)
    {
        $bin = array();
        $max = 0;
        for($i=0; strlen($text)>$i; $i++) {
            $bin[] = decbin(ord($text[$i]));
            if(strlen($bin[$i]) < 8)
            {
                $countNull = 8 - strlen($bin[$i]);
                $stringNull = '';
                for($j = 0; $j < $countNull; $j++) {
                    $stringNull .= '0';
                }
                $bin[$i] = $stringNull.$bin[$i];
            }
            if(strlen($bin[$i]) > 8 && strlen($bin[$i]) > $max)
            {
                $max = strlen($bin[$i]);
            }
        }
        return implode('',$bin);
    }

    function ASCIIBinText2($bin)
    {
        $text = array();
        $bin = explode(" ", $bin);
        for($i=0; count($bin)>$i; $i++)
            $text[] = chr(bindec($bin[$i]));
        return implode($text);
    }
}
