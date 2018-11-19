<?php

namespace Olezhik\Parser;

use DiDom\Document;
use League\Csv\Writer;
use League\Csv\Reader;
use DiDom\Element;
use Stichoza\GoogleTranslate\TranslateClient;
/**
 * Description of ParseI28
 *
 * @author oleg
 */
class ParseProductsi28 extends Parsei28{
    
    protected $file;
    
    protected $limit=10;

    protected $csv;
    
    protected $translate;
    
    protected $countAttributes = 3;


    protected $selectors = [
        'ID'            => 'input[name="products_id"]',
        'Name'          => '#productName font font',
        'Price'         => '#productPrices font font',
        'Categories'    => '#navBreadCrumb'
    ];
    
    public function __construct() {
        $this->translate = new TranslateClient(null, "en");
    }
    

    protected function getSelector($str) {
        return isset($this->selectors[$str]) ? $this->selectors[$str] : null;
    }

    public function __invoke($sFile, $sFileCSV) {
        $this->file = @fopen($sFile, "r");
        
        $this->prepareCSV();      
        
        if (!$this->file) {
            return false;
        }
        
        while(($str = $this->readString()) !== false){
            echo "Парсинг ".$str;
            $this->parseProduct($str);            
        }        
        
        fclose($this->file);
        
        file_put_contents($sFileCSV, $this->csv->getContent());
    }
    
    public function readString() {
        if (!$this->file) {
            return false;
        }
        if (($buffer = fgets($this->file, 4096)) === false) {
            return false;
        }
        if (feof($this->file)) {
            fclose($this->file);
        }
        return $buffer;
    }
    
    public function parseProduct($url) {
        $document = new Document($url, true);
        
        if(!$document){
            return false;
        }
        
        $elements = [];
        
        foreach ($this->selectors as $key => $selector) {
            $elements[$key] = $this->getElement($document, $selector);
        }
        
        $aRow = [
            $elements["ID"]->attr('value'),
            "simple",
            //"SKU",
            $this->translate->translate($elements["Name"]->text()), //"Name",
            "1",//"Published",
            "1", //Is featured?",
            "visible", //Visibility in catalog",
            //"Short description",
            //"Description",
            //"Date sale price starts",
            //"Date sale price ends",
            "taxable", //Tax status",
            //"Tax class",
            "1", //In stock?",
            //"Stock",
            "0",//Backorders allowed?",
            "0",//Sold individually?",
            //"Weight (lbs)",
            //"Length (in)",
            //"Width (in)",
            //"Height (in)",
            "1",//Allow customer reviews?",
            //"Purchase note",
            //"Sale price",
            $this->pregPrice($elements["Price"]->text()),//"Regular price",
            $this->getCategories($elements["Categories"]),//"Categories",
            //"Tags"	,
            //"Shipping class",
            $this->getImages($document),//"Images",
            //"Download limit",
            //"Download expiry days",
            //"Parent",
            ///"Grouped products",
            //"Upsells",
            //"Cross-sells",
            //"External URL",
            //"Button text",
            "0",//Position",
//            "Attribute 1 name",
//            "Attribute 1 value(s)",
//            "Attribute 1 visible",
//            "Attribute 1 global",
//            "Attribute 2 name",
//            "Attribute 2 value(s)",
//            "Attribute 2 visible",
//            "Attribute 2 global",
//            "Attribute 3 name",
//            "Attribute 3 value(s)",
//            "Attribute 3 visible",
//            "Attribute 3 global",
            //"Meta: _wpcom_is_markdown",
            ////"Download 1 name",
            //"Download 1 URL",
            //"Download 2 name",
            //"Download 2 URL"
        ];
        
        $attributes = $this->getAttributes($document);
        
        for($i = 0; $i < $this->countAttributes;$i++) {
            if(isset($attributes[$i])){
                $attribute = $attributes[$i];
            } else {
                $attribute = ['label' => '', 'values' => []];
            }
            $aRow[] = $attribute['label'];
            $aRow[] = join(', ', $attribute['values']);
            $aRow[] = "1";
            $aRow[] = "1";
        }
                
        $this->csv->insertOne($aRow);
    }
    
    public function getAttributes($document) {
        $attributes = [];
        
        $elAttributes = $document->find('#productAttributes');
        if(count($elAttributes) < 1){
            return false;
        }
        $elAttributes = $elAttributes[0];
        
        $wrappersAttribute = $elAttributes->find('.wrapperAttribsOptions');
        if(count($wrappersAttribute) < 1){
            return false;
        }
        
        foreach ($wrappersAttribute as $wrapperAttribute) {
            $attribute = [ 'values'=>[] ];
            $attribute['label'] = $wrapperAttribute->first('label')->text();
            $options = $wrapperAttribute->find('option');
            foreach ($options as $option) {
                $attribute['values'][] = $option->text();
            }  
            $attributes[] = $attribute;
        }
        return $attributes;
    }
    
    public function getImages($document) {
        $images = [];
        $images1 = $document->find('#productMainImage a');
        $images2 = $document->find('#productAdditionalImages a');
        $aImages = array_merge($images1, $images2);
        foreach ($aImages as $el) {
            $images[] = $el->attr('href');
        }
        return join(", ", $images);
    }
    
    protected function pregPrice($str) {
        $matches = [];
        preg_match("/^(\d+\,\d+)?$/", $str, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return "";
    }
    
    public function getCategories($elem) {
        $categoriesEl = $elem->children();
        $categories = [];
        $i = 0;
        foreach ($categoriesEl as $el) {
            if($i){
                $categories[] = $el->text();
            }
            $i++;
        }
        return join(" > ", $categories);
    }
    
    protected function getElement($document, $selector) {
        if(count($elements = $document->find($selector)) > 0){
            return $elements[0];
        }
        return $document->createElement('div');
    }
    
    protected function prepareCSV() {
        $this->csv = Writer::createFromString('');
        
        $aRow =[
            "ID",
            "Type",
            //"SKU",
            "Name",
            "Published",
            "Is featured?",
            "Visibility in catalog",
            //"Short description",
            //"Description",
            //"Date sale price starts",
            //"Date sale price ends",
            "Tax status",
            //"Tax class",
            "In stock?",
            //"Stock",
            "Backorders allowed?",
            "Sold individually?",
            //"Weight (lbs)",
            //"Length (in)",
            //"Width (in)",
            //"Height (in)",
            "Allow customer reviews?",
            //"Purchase note",
            //"Sale price",
            "Regular price",
            "Categories",
            //"Tags"	,
            //"Shipping class",
            "Images",
            //"Download limit",
            //"Download expiry days",
            //"Parent",
            //"Grouped products",
            //"Upsells",
            //"Cross-sells",
            //"External URL",
           //"Button text",
            "Position",
//            "Attribute 1 name",
//            "Attribute 1 value(s)",
//            "Attribute 1 visible",
//            "Attribute 1 global",
//            "Attribute 2 name",
//            "Attribute 2 value(s)",
//            "Attribute 2 visible",
//            "Attribute 2 global",
//            "Attribute 3 name",
//            "Attribute 3 value(s)",
//            "Attribute 3 visible",
//            "Attribute 3 global",
            //"Meta: _wpcom_is_markdown",
//            "Download 1 name",
//            "Download 1 URL",
//            "Download 2 name",
//            "Download 2 URL"
        ];
        
        for($i = 1; $i<($this->countAttributes+1);$i++) {
            $aRow[] = "Attribute {$i} name";
            $aRow[] = "Attribute {$i} value(s)";
            $aRow[] = "Attribute {$i} visible";
            $aRow[] = "Attribute {$i} global";
        }
                
        $this->csv->insertOne($aRow);
        
        
    }
    
}
