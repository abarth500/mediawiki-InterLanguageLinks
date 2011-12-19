<?php
# SimpleInterLanguageLink MediaWiki extension v.1
#    Author: Shohei Yokoyama (Shizuoka Univ. Japan)
# 
#-Install
# 1. Save this code as $MediaWikiRoot/SimpleInterLanguageLinks/SimpleInterLanguageLinks.php
# 2. Append "require_once "$IP/extensions/SimpleInterLanguageLinks/SimpleInterLanguageLinks.php";" to LocalSetting.php
#
#-Configure (in LocalSetting.php)
#  -1st language of your wiki is defined as $wgLanguageCode
#
#  -To add foreign languages of your wiki
#    $wfSimpleInterLanguageLinks_ForeignLanguages = array("ja","fr");
#    // Do not include $wgLanguageCode !
#
#  -Set true to generate a parent page navigation includes language code
#    $wfSimpleInterLanguageLinks_MagicNavigation = true; //default
#
#  -Set true to overwrite page title with only sugpage title
#    $wfSimpleInterLanguageLinks_MagicTitle = true;      //default
#
#-Usage 
#   In case of
#      $wgLanguageCode = "en";
#      $wfSimpleInterLanguageLinks_ForeignLanguages = array("ja","fr");
#
# Page title mast be named as
#    English Page:  Page_Name        -> links of fr and ja is displayed
#    Japanese Page: Page_Name/ja     -> links of en and fr is displayed
#    French Page:   Page_Name/fr     -> links of en and ja is displayed
#
# Subpage title mast be named as
#    English Page:  Page_Name/Subpage_Name
#    Japanese Page: Page_Name/Subpage_Name/ja
#    French Page:   Page_Name/Subpage_Name/fr


$wgExtensionCredits['parserhook'][] = array(
        'name' => 'SimpleInterLanguageLinks',
        'description' => 'Append inter-language links to Sidebar',
        'version' => 1,
        'author' => 'Shohei Yokoyama',
        'url' => 'http://shohei.yokoyama.ac/'
);


$wgHooks['SkinTemplateOutputPageBeforeExec'][] = "wfSimpleInterLanguageLinks_SkinTemplateOutputPageBeforeExec";
$wgHooks['SkinSubPageSubtitle'][] = 'wfSimpleInterLanguageLinks_SkinSubPageSubtitle';
if(!isset($wfSimpleInterLanguageLinks_ForeignLanguages)){
	$wfSimpleInterLanguageLinks_ForeignLanguages = array();
}
if(!isset($wfSimpleInterLanguageLinks_MagicTitle)){
	$wfSimpleInterLanguageLinks_MagicTitle = true;
}
if(!isset($wfSimpleInterLanguageLinks_MagicNavigation)){
	$wfSimpleInterLanguageLinks_MagicNavigation = true;
}

function wfSimpleInterLanguageLinks_SkinTemplateOutputPageBeforeExec($skin, $tpl){
	global $wfSimpleInterLanguageLinks_ForeignLanguages,$wgContLang,$wgLanguageCode;
	$title = $skin->getRelevantTitle()->getDBkey();
	$namespace = MWNamespace::getCanonicalName($skin->getRelevantTitle()->getNamespace());
	$nav  = explode("/",$title);
	$lang = $nav[count($nav)-1];
	if(array_search($lang,$wfSimpleInterLanguageLinks_ForeignLanguages)===FALSE){
		$lang = $wgLanguageCode;
	}else{
		array_pop($nav);
		$title = implode("/",$nav);
	}
	$language_urls = array();
	if($lang != $wgLanguageCode){
		$title = Title::makeTitle($namespace,$title);
		if($title->exists()){
			$language_urls[] = array(
				'href' => $title->getFullURL(),
				'text' => $wgContLang->getLanguageName( $wgLanguageCode ),
				'class' => 'interwiki-' . $wgLanguageCode
			);
		}
	}
	foreach($wfSimpleInterLanguageLinks_ForeignLanguages as $lan){
		if($lan != $lang){
			$title = Title::makeTitle($namespace,$title."/".$lan);
			if($title->exists()){
				$language_urls[] = array(
					'href' => $title->getFullURL(),
					'text' => $wgContLang->getLanguageName( $lan ),
					'class' => 'interwiki-' . $lan
				);
			}
		}
	}
	if(count($language_urls)) {
		$tpl->setRef( 'language_urls', $language_urls);
	} else {
		$tpl->set('language_urls', false);
	}
	return true;
}

function wfSimpleInterLanguageLinks_SkinSubPageSubtitle($subpages, $skin){
	global $wfSimpleInterLanguageLinks_ForeignLanguages,$wgLanguageCode,$wgScriptPath;
	$navi = explode("/",$skin->getRelevantTitle()->getDBkey());
	$namespace = MWNamespace::getCanonicalName($skin->getRelevantTitle()->getNamespace());
	if($namespace != ""){
		$namespace .= ":";
	}
	if(array_search($navi[count($navi)-1],$wfSimpleInterLanguageLinks_ForeignLanguages)===FALSE){
		$lang = "";
	}else{
		$lang = "/".array_pop($navi);
	}
	$pageTitle = array_pop($navi);
	$NAVI = array();
	for($c = 1;$c <= count($navi);$c++){
		$title = Title::newFromText($namespace.implode("/",array_slice($navi,0,$c)).$lang);
		if($title->exists()){
			array_push($NAVI,'<a href="'.$title->getFullURL().'">'.$navi[$c-1].'</a>');
		}else{
			array_push($NAVI,$navi[$c-1]);
		}
	}
	$main = Title::makeTitle(NS_MEDIAWIKI,"Mainpage");
	if($main->exists()){
		$main = new Article($main);
		$main = $main->getContent();
	}else{
		$main = "";
	}
	$subpages .= '&gt;&gt;<a href="'.$wgScriptPath.'/'.$main.'">Top</a> ';
	if(count($NAVI)>0){
		$subpages .= "&gt; ";
	}
	$subpages .= implode(" &gt; ",$NAVI);
	$subpages .= '<script>(function(){var t = document.getElementById("firstHeading");if(t.innerHTML == "'.$pageTitle.'"){t.innerHTML = "'.$pageTitle.'";}})();</script>';
	return false;
}
?>