<?php

namespace App\Service;

use Exception;

class ArtVariationsHelper
{
    public function getHumanReadableArtVariations(string $artVariations): string
    {
        if ($artVariations === '{}') {
            return '';
        } elseif ($artVariations === '{EA}') {
            return 'Extended Art';
        } elseif ($artVariations === '{AA}') {
            return 'Alternate Art';
        } elseif ($artVariations === '{FA}') {
            return 'Full Art';
        } elseif ($artVariations === '{AB}') {
            return 'Alternate Border';
        } elseif ($artVariations === '{AT}') {
            return 'Alternate Text';
        } elseif ($artVariations === '{AA,EA}') {
            return 'Alternate Art, Extended Art';
        } elseif ($artVariations === '{AA,FA}') {
            return 'Alternate Art, Full Art';
        } elseif ($artVariations === '{AB,EA}') {
            return 'Alternate Border, Extended Art';
        } elseif ($artVariations === '{AA,AB}') {
            return 'Alternate Art, Alternate Border';
        } elseif ($artVariations === '{AB,AT}') {
            return 'Alternate Border, Alternate Text';
        } elseif ($artVariations === '{AA,AT}') {
            return 'Alternate Art, Alternate Text';
        } elseif ($artVariations === '{HS}') {
            return 'HS';
        } elseif ($artVariations === '{AA,HS}') {
            return 'Alternate Art, HS';
        }
        
        return '';
    }
}
