<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
class kudagoAPI{
	const APIversion = 'v1.3';
	const APIlang = 'ru';
	public function getPlaceCategories(){
		$APIrequest = file_get_contents("https://kudago.com/public-api/".self::APIversion."/place-categories/?lang=".self::APIlang."&order_by=slug&fields=name,slug");
		$APIrequest =json_decode($APIrequest, true);
		return $APIrequest;
	}
	public function getPlaces($urlSlug){
		$APIrequest2 = file_get_contents("https://kudago.com/public-api/".self::APIversion."/places/?lang=".self::APIlang."&fields=id,title,slug,address,location,site_url,is_closed&order_by=id&text_format=text&location=msk&categories=".$urlSlug."");
		$APIrequest2 =json_decode($APIrequest2, true);
		return $APIrequest2['results'];
	}
    public function addNewPlaceCategory($iblockID){
        foreach(self::getPlaceCategories() as $placeCategory){
			$arParams = array("replace_space"=>"-","replace_other"=>"-");
			$translitName = Cutil::translit($placeCategory['name'],"ru",$arParams);
			if(CModule::IncludeModule("iblock")){	

				$bs = new CIBlockSection;
				$sectionParams = Array(
				  "ACTIVE" => 'Y',
				  "IBLOCK_SECTION_ID" => '',
				  "IBLOCK_ID" => $iblockID,
				  "CODE" => $translitName,
				  "NAME" => $placeCategory['name'],
				  "SORT" => 100,
				  "PICTURE" => '',
				  "DESCRIPTION" => '',
				  "DESCRIPTION_TYPE" => 'text'
				  );
				$bs->Add($sectionParams);
			}
		}
	}
    public function addNewCategoriesAndPlaces($iblockID){
        foreach(self::getPlaceCategories() as $placeCategory){
			$arParams = array("replace_space"=>"-","replace_other"=>"-");
			$translitName = Cutil::translit($placeCategory['name'],"ru",$arParams);
			if(CModule::IncludeModule("iblock")){	

				$bs = new CIBlockSection;
				$arFields = Array(
				  "ACTIVE" => 'Y',
				  "IBLOCK_SECTION_ID" => '',
				  "IBLOCK_ID" => $iblockID,
				  "CODE" => $translitName,
				  "NAME" => $placeCategory['name'],
				  "SORT" => 100,
				  "PICTURE" => '',
				  "DESCRIPTION" => '',
				  "DESCRIPTION_TYPE" => 'text'
				  );
				$sectionID = $bs->Add($arFields);
				self::addNewPlaces($sectionID,$placeCategory['slug'],$iblockID);
			}
		}
	}
    public function addNewPlaces($sectionID,$urlSlug,$iblockID){
        foreach(self::getPlaces($urlSlug) as $places){
			$arParams = array("replace_space"=>"-","replace_other"=>"-");
			$placeTitle = Cutil::translit($places['title'],"ru",$arParams);
        	if(CModule::IncludeModule("iblock")){
        	$el = new CIBlockElement;
			$elementParams = Array(
			  "IBLOCK_SECTION_ID" => $sectionID,          // элемент лежит в корне раздела
			  "IBLOCK_ID"      => $iblockID,
			  "CODE"      => $placeTitle.'_'.$sectionID,
			  "PROPERTY_VALUES"=> $PROP,
			  "NAME"           => $places['title'],
			  "ACTIVE"         => "Y",            // активен
			  "PREVIEW_TEXT"   => "",
			  "DETAIL_TEXT"    => "",
			  "DETAIL_PICTURE" => "");
			$el->Add($elementParams);
		}
		}
    }
}
$kudagoAPI = new kudagoAPI;
//$kudagoAPI->addNewPlaceCategory($iblockID,$urlSlug);//Создаём только категории
$kudagoAPI->addNewCategoriesAndPlaces(5);//Создаём категории и элементы
//$kudagoAPI->addNewPlaces($sectionID,$urlSlug,$ibclockID);//Создаём только элементы для определённого раздела
?>