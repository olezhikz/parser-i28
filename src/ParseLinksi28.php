<?php

namespace Olezhik\Parser;

use DiDom\Element;
use DiDom\Document;

/**
 * Description of Parsei28
 *
 * @author oleg
 */
class ParseLinksi28 extends Parsei28 {
    
    public $count = 0;

    public function __invoke( $aLinksPages ) {
        foreach($aLinksPages as $sLinkPage) {
            $this->listPage($sLinkPage);
        }
        
        return true;
    }
    
    public function listPage($sLinkPage) {
        echo "\nЗагрузка страницы ".$sLinkPage;
        
        $document = new Document($sLinkPage, true);
        
        $aLinks = $this->getLinksProducts($document);
        $this->write($aLinks);
        
        if($this->hasListing($document)){
            if ($sLinkPage = $this->getNextPage($document)){
                $this->listPage($sLinkPage);                
            } 
        }
    }
    
    public function write($aLinks) {
        foreach ($aLinks as $sLink) {
            file_put_contents(dirname(__DIR__) . '/products.txt', $sLink.PHP_EOL, FILE_APPEND);
        }
    }
    
    public function hasListing(Document $document) {
        return $document->has('#productsListingListingTopLinks');
    }
    
    public function getNextPage(Document $document) {
        
        $eNextElement = $document->find('#productsListingListingTopLinks a[title=" 下一页 "]');
        
        if (!count($eNextElement)) {
            return null;
        }
        
        $eNextElement = $eNextElement[0];
        
        return $eNextElement->attr('href');
    }
    
    public function getLinksProducts(Document $document) {
        $aElements = $document->find('.productListing-data:first-child a');
        $this->count += count($aElements);
        echo "\rНайдено ".$this->count." продуктов";
        return $this->getUrlsElements($aElements);
    }
}
