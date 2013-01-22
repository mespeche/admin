<?php

use Symfony\Component\HttpFoundation\Request;

class ActionsAdminProduct extends ActionsAdminBase
{
    private static $instance = false;
    
    protected function __construct() {}
    /**
     * 
     * @return \ActionsAdminProduct
     */
    public static function getInstance(){
        if(self::$instance === false) self::$instance = new ActionsAdminProduct();
        
        return self::$instance;
    }
    
    public function action(Request $request){
        switch($request->get('action')){
            case "modifier" : 
                $productAdmin = ProductAdmin::getInstanceByRef($request->request->get('ref'));
                $productAdmin->modify(
                        $request->request->get('lang', ActionsLang::instance()->get_id_langue_courante()),
                        $request->request->get('prix', 0),
                        $request->request->get('prix2', 0),
                        $request->request->get('ecotaxe', 0),
                        $request->request->get('promo'),
                        $request->request->get('rubrique'),
                        $request->request->get('nouveaute'),
                        $request->request->get('perso'),
                        $request->request->get('poids'),
                        $request->request->get('stock'),
                        $request->request->get('tva'),
                        $request->request->get('ligne'),
                        $request->request->get('titre'),
                        $request->request->get('chapo'),
                        $request->request->get('description'),
                        $request->request->get('postscriptum'),
                        $request->request->get('urlsuiv'),
                        $request->request->get('urlreecrite'),
                        $this->getCaracteristique($request, $productAdmin),
                        $this->getDeclinaison($request, $productAdmin),
                        $this->getImages($request, $productAdmin),
                        $this->getDocuments($request, $productAdmin),
                        $request->request->get('tab')
                );
                break;
            case "modifyAttachementPosition":
                ProductAdmin::getInstanceByRef($request->query->get('ref'))->changeAttachementPosition(
                        $request->query->get('attachement'),
                        $request->query->get('attachement_id'),
                        $request->query->get('direction'),
                        $request->query->get('lang'),
                        $request->query->get('tab')
                );
                break;
            case "deleteAttachement":
                //produit_modifier.php?ref=chh0001&action=deleteAttachement&attachement=image&attachement_id=19&lang=1
                ProductAdmin::getInstanceByRef($request->query->get('ref'))->deleteAttachement(
                        $request->query->get('attachement'),
                        $request->query->get('attachement_id'),
                        $request->query->get('lang'),
                        $request->query->get('tab')
                );
                break;
        }
    }
    
    /**
     * 
     * search caracteristique in request and return an array. Indexes are the id of each caracteristique and value are the caracdisp selected
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Array
     */
    protected function getCaracteristique(Request $request, \ProductAdmin $product)
    {        
        $return = array();
        
        if($product->id == ''){
            return $return;
        }
        
        $query = 'select caracteristique from '.Rubcaracteristique::TABLE.' where rubrique='.$product->rubrique;
        
        foreach($product->query_liste($query) as $caracteristique)
        {
            if(false !== $carac = $request->request->get('caract'.$caracteristique->caracteristique, false)){                
                $return[$caracteristique->caracteristique] = $carac;
            }
        }
        
        return $return;
    }
    
    /**
     * 
     * search declinaison in request and return an array.
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Array
     */
    protected function getDeclinaison(Request $request, \ProductAdmin $product)
    {
        $return = array();
        
        if($product->id == ''){
            return $return;
        }
        
        $query = "select dc.id from ".Declidisp::TABLE." dc left join ".Rubdeclinaison::TABLE." rd on rd.declinaison=dc.declinaison where rd.rubrique=".$product->rubrique;
        
        $return = $this->extractResult($request, $product->query_liste($query), array(
            "stock" => "stock",
            "surplus" => "surplus"
        ));
        
        return $return;
    }
    
    protected function getImages(Request $request, \ProductAdmin $product)
    {
        $return = array();
        
        if($product->id == ''){
            return $return;
        }
        
        $query = 'select id from '.Image::TABLE.' where produit='.$product->id;

        
        
        $return = $this->extractResult($request, $product->query_liste($query), array(
            "titre" => "photo_titre_",
            "chapo" => "photo_chapo_",
            "description" => "photo_description_"
        ));
        
        return $return;
    }
    
    protected function getDocuments(Request $request, \ProductAdmin $product)
    {
        $return = array();
        
        if($product->id == ''){
            return array();
        }
        
        $query = "select id from ".Document::TABLE.' where produit='.$product->id;
        
        $return = $this->extractResult($request, $product->query_liste($query), array(
            "titre" => "document_titre_",
            "chapo" => "document_chapo_",
            "description" => "document_description_"
        ));
        
        return $return;
    }
    
    
}