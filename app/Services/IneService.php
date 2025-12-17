<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Http\UploadedFile;
use App\Utils\IneTextParser;
use Illuminate\Support\Str;

class IneService
{
    public function process(UploadedFile $image): array {
        $path = storage_path('app/ine');

        if(!is_dir($path)){
            mkdir($path, 0777, true);
        }

        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
        $fullpath = $image->move($path, $filename);

        try {
            $text = (new TesseractOCR($fullpath))
                ->lang('spa')
                ->psm(6)
                ->run();
            
            // \Log::info('Texto: ', ['text' => $text]);
            // echo $text;
            

            $data = IneTextParser::parse($text);

            if(empty($data['curp'])){
                throw new \Exception('No se extrajo la curp. Habra que validar la calidad de la imagen');
            }
            return $data;
        }finally {
            if(file_exists($fullpath)){
                unlink($fullpath);
            }
        }

    }
}