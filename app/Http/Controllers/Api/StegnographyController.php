<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class StegnographyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return response()->json('agaya');

        // return json_encode('dsa');
    }

    // public function LSBEncodeCrypt(Request $request)
    public function LSBEncodeCrypt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'msg' => 'required',
            'password' => 'required',
            'encImage' => 'required'
        ]);
        if($validator->passes())
        {      
            $pictures = $request->encImage;
            $pictures = str_replace('data:image/png;base64,', '', $pictures);
            $pictures = str_replace(' ', '+', $pictures);
            $imageName = str_random(10).'.'.'png';
            \File::put(public_path(). '/' . $imageName, base64_decode($pictures));
            // return response($pictures);
            // dd($pictures);
            $original = preg_replace('/data:image\/\w+;base64,/', '', $pictures);
            $original = base64_decode($original);
            $imageOriginal = imagecreatefromstring($original);
            $x_dimension = imagesx($imageOriginal); //height
            $y_dimension = imagesy($imageOriginal); //width
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
            
            $sign = $this->textBinASCII2('gravitation');
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
            $base64 = 'data:image/png;base64,' . $image_string;
            imagedestroy($imageCrypto);
            return response()->json(['encImage' => $base64]);
        }
        else
        {
            return response()->json($validator->messages(), 200);
        }
        // dd($base64);
    }

    public function LSBDecodeCrypt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required',
            'password' => 'required'
        ]);

        if($validator->passes())
        {       
            $pictures = $request->data;
            $original = preg_replace('/data:image\/\w+;base64,/', '', $pictures);
            $original = base64_decode($original);
            $imageOriginal = imagecreatefromstring($original);
            
            $x_dimension = imagesx($imageOriginal); //height
            $y_dimension = imagesy($imageOriginal); //width
            // dd($y_dimension);

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
            // dd($binaryString);

            $iv = "1234567812345678";
            $key = $request->password;
            // dd($key);
            
            $sign = $this->textBinASCII2('gravitation');
            $lengthSign = strlen($sign);
            $position = strpos($binaryString, $sign);
            $lengthBinData = mb_substr($binaryString, 0, $position);
            $lengthData = $this->stringBinToStringChars8($lengthBinData);
            $positionData = $position + $lengthSign;
            // dd($lengthData);
            $binaryData = mb_substr($binaryString, $positionData, $lengthData);
            // dd($binaryData);
            
            $cryptoString = $this->stringBinToStringChars8($binaryData);
            $output = openssl_decrypt($cryptoString, 'AES-256-CFB', $key, OPENSSL_RAW_DATA, $iv);
            // dd($cryptoString);
            return response()->json(['text' => $output]);
        }
        else
        {
            return response()->json($validator->messages(), 200);
        }
    }

    public function stringBinToStringChars8($strBin)
    {
        // dd('fun '. $strBin);
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
        // dd('fun1 '. $text);
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
        // dd('fun2 '. $bin);
        $text = array();
        $bin = explode(" ", $bin);
        for($i=0; count($bin)>$i; $i++)
            $text[] = chr(bindec($bin[$i]));
        return implode($text);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
