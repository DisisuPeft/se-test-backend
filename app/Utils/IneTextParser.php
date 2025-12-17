<?php

namespace App\Utils;

class IneTextParser
{
    public static function parse(string $text): array {
        $text = strtoupper(trim($text));
        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $text)),
            fn($line) => !empty($line) && strlen($line) > 1
        ));

        $data = [
            'apellido_paterno' => self::clean(self::extractApellidoPaterno($lines)),
            'apellido_materno' => self::clean(self::extractApellidoMaterno($lines)),
            'nombre' => self::clean(self::extractNombre($lines)),
            'curp' => self::extractCurp($text),
            'domicilio' => self::cleanDomicilio(self::extractDomicilio($lines))
        ];

        return $data;
    } 

    private static function clean(?string $text): ?string{
        if (!$text) return null;

        $text = preg_replace('/[^A-ZÑÁÉÍÓÚü\s]/', '', $text);
        

        $words = explode(' ', $text);
        $words = array_filter($words, fn($word) => strlen($word) > 1);
        

        $cleaned = [];
        $prev = null;
        foreach ($words as $word) {
            if ($word !== $prev) {
                $cleaned[] = $word;
            }
            $prev = $word;
        }
        
        $cleaned = array_filter($cleaned, function($word) {
            return !preg_match('/(.)\1{2,}/', $word); // AAA, BBB, etc.
        });

        return trim(implode(' ', $cleaned));
    }

    private static function cleanDomicilio(?string $text): ?string{
        if (!$text) return null;

        $text = preg_replace('/[^A-ZÑ0-9,\s]/', '', $text);
        
        $words = explode(' ', $text);
        $words = array_filter($words, fn($word) => strlen($word) > 2);
        

        $cleaned = [];
        $prev = null;
        foreach ($words as $word) {
            if ($word !== $prev) {
                $cleaned[] = $word;
            }
            $prev = $word;
        }

        return trim(implode(' ', $cleaned));
    }

    private static function extractApellidoPaterno(array $lines): ?string {
        foreach ($lines as $i => $line) {
            if (str_contains($line, 'NOMBRE')) {

                return $lines[$i + 1] ?? null;
            }
        }
        return null;
    }

    private static function extractApellidoMaterno(array $lines): ?string {
        foreach ($lines as $i => $line) {
            if (str_contains($line, 'NOMBRE')) {
                return $lines[$i + 2] ?? null;
            }
        }
        return null;
    }

    private static function extractNombre(array $lines): ?string {
       foreach ($lines as $i => $line) {
            if (str_contains($line, 'NOMBRE')) {
                return $lines[$i + 3] ?? null;
            }
        }
        return null;
    }

    private static function extractCurp(string $text): ?string
    {
        $pattern = '/[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d/';
        
        if (preg_match($pattern, $text, $matches)) {
            $curp = $matches[0];
            
            if (strlen($curp) === 18) {
                return $curp;
            }
        }
        
        if (preg_match('/CURP\s+([A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d)/', $text, $matches)) {
            $curp = $matches[1];
            
            if (strlen($curp) === 18) {
                return $curp;
            }
        }
        
        return null;
    }

    private static function extractDomicilio(array $lines): ?string{
        foreach ($lines as $i => $line) {
            if (str_contains($line, 'DOMICILIO')) {
                $siguiente = $lines[$i + 1] ?? null;
                if ($siguiente) {
                    return preg_replace('/[^A-Z0-9,\s]/', '', $siguiente);
                }
            }
        }
        return null;
    }
}