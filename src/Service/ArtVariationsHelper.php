<?php

namespace App\Service;

class ArtVariationsHelper
{
    /**
     * As there is no table containing a description/name of a certain art variation (property of CardPrinting)
     * let's create our own logic to convert the abbreviation to a human readable description.
     * 
     * @param string $artVariations String containing the value of CardPrinting.artVariation. Example: {EA}
     * 
     * @return string A human readable description, like 'Extended Art'
     */
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
