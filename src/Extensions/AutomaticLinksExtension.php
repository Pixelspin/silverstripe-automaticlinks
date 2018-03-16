<?php

namespace Pixelspin\Automaticlinks\Extensions;

use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\FieldList;
use SilverStripe\View\TemplateGlobalProvider;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Forms\LiteralField;

class AutomaticLinksExtension extends DataExtension implements TemplateGlobalProvider {

    private static $db = [
        'AutomaticLinksKeywords' => 'Text'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $info = '<p>Insert a list of comma seperated keywords, these keywords are used in content on other pages to create links to this page.</p>';
        $fields->addFieldToTab('Root.AutomaticLinks', LiteralField::create('AutomaticLinksKeywordsInfo', $info));
        $fields->addFieldToTab('Root.AutomaticLinks', TextareaField::create('AutomaticLinksKeywords', 'Keywords')->setDescription('Seperate keywords with a comma.'));
    }

    public static function AddAutomaticLinks($content){
        if(!$content){
            return $content;
        }
        $currentPage = Controller::curr();
        $pagesWithKeywords = SiteTree::get()->exclude('AutomaticLinksKeywords', null)->exclude('ID', $currentPage->ID);
        $keywords = [];
        foreach($pagesWithKeywords as $page){
            $pageKeywords = explode(',', $page->AutomaticLinksKeywords);
            foreach($pageKeywords as $keyword){
                $keywords[$keyword] = $page->Link();
            }
        }
        if(!count($keywords)){
            return $content;
        }
        foreach($keywords as $keyword => $link){
            $content = str_ireplace(' ' . $keyword . ' ', ' <a href="'.$link.'">'.$keyword.'</a> ', $content);
            $content = str_ireplace(' ' . $keyword . '.', ' <a href="'.$link.'">'.$keyword.'</a>.', $content);
            $content = str_ireplace(' ' . $keyword . ',', ' <a href="'.$link.'">'.$keyword.'</a>,', $content);
        }
        return DBField::create_field(DBHTMLText::class, $content);
    }

    public static function get_template_global_variables()
    {
        return [
            'AddAutomaticLinks'
        ];
    }

}
