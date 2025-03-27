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
    
    /**
     * Same as $this->getHumanReadableArtVariations(), instead it returns a shortened 'code' to be able to
     * display art variations on smaller viewports. 
     * 
     * @param string $artVariations String containing the value of CardPrinting.artVariation. Example: {EA}
     * 
     * @return string A shortened human readable description, like 'EA'
     */
    public function getShortenedArtVariations(string $artVariations): string
    {
        if ($artVariations === '{}') {
            return '';
        } elseif ($artVariations === '{EA}') {
            return 'EA';
        } elseif ($artVariations === '{AA}') {
            return 'AA';
        } elseif ($artVariations === '{FA}') {
            return 'FA';
        } elseif ($artVariations === '{AB}') {
            return 'AB';
        } elseif ($artVariations === '{AT}') {
            return 'AT';
        } elseif ($artVariations === '{AA,EA}') {
            return 'AA, EA';
        } elseif ($artVariations === '{AA,FA}') {
            return 'AA, FA';
        } elseif ($artVariations === '{AB,EA}') {
            return 'AB, EA';
        } elseif ($artVariations === '{AA,AB}') {
            return 'AA, AB';
        } elseif ($artVariations === '{AB,AT}') {
            return 'AB, AT';
        } elseif ($artVariations === '{AA,AT}') {
            return 'AA, AT';
        } elseif ($artVariations === '{HS}') {
            return 'HS';
        } elseif ($artVariations === '{AA,HS}') {
            return 'AA, HS';
        }
        
        return '';
    }
}
