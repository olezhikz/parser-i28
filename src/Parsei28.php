<?php

namespace Olezhik\Parser;

use DiDom\Element;

/**
 * Description of Parsei28
 *
 * @author oleg
 */
abstract class Parsei28 {
    
    public function getUrlsElements( $aElements ) {
        $aLinks = [];

        foreach($aElements as $element) {
            if($element instanceof Element){
                $aLinks[] = $element->attr('href');
            }
        }
        
        return $aLinks;
    }
}
