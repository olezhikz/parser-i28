<?php


namespace Olezhik\Parser;

use DiDom\Document;

/**
 * Description of ParseCategoriesi28
 *
 * @author oleg
 */
class ParseCategoriesi28 extends Parsei28 {
    
    public $limit = 40;

    private $count = 0;
    
    public $productsOne = [];


    public function __construct() {
        //echo __DIR__;
    }

    public function getLinksCategory( $document) {
        
        $links = $document->find('#categoriesContent .category-top');
        echo "\nНайдено верхних категорий ". count($links);
        return $this->getUrlsElements($links);
    }
    
    public function getLinksSubcategory( $document) {
        $links = $document->find('.categoryListBoxContents a');

        return $this->getUrlsElements($links);
    }
    
    public function hasSubcategories(Document $document) {
        return $document->has('#indexCategoriesHeading');
    }
    
    public function __invoke($url) {
        $aLinks = [];
        
        $document = new Document($url, true);
        $aLinksCategory = $this->getLinksCategory($document);        
        
        foreach ($aLinksCategory as $sLinkCategory) {
            $this->getResultLinks($sLinkCategory, $aLinks);
        }       
        
        return $aLinks;
    }
    
    public function getResultLinks($url, &$aLinks) {
        echo "\n{$url}";
        $document = new Document($url, true);
        if($this->hasSubcategories($document)){
            $aLinksSubcategory = $this->getLinksSubcategory($document);
            foreach ($aLinksSubcategory as $sLinkSubcategory) {
                if(($this->count++) > $this->limit){
                    break;
                }
                $this->getResultLinks($sLinkSubcategory, $aLinks);
            }
        }else{
            if($document->has('#productsListingTopNumber')){
                if (array_search($url, $aLinks) === false) {
                   $aLinks[] = $url; 
                }                
            }
            if($document->has('#productinfoBody')){
                $this->productsOne[] = $url;
            }
            echo "\rНайдено ".count($aLinks)." категорий и ".count($this->productsOne)." продуктов";
        }
    }
}
