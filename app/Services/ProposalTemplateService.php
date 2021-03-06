<?php

namespace App\Services;

use App\Proposal;

class ProposalTemplateService
{
    const TEMPLATE_DATA = [
        Proposal::TEMPLATE_TYPE_EVOLUTION_MARKETING => [
            'prepared_by' => 'Evolution Marketing',
            'phone' => "585-981-8463",
            'email' => "info@evolutionmarketing.com",
            'logo' => "evolution_marketing.logo"
        ],
        Proposal::TEMPLATE_TYPE_EVOLUTION_MARKETING_FLORIDA => [
            'prepared_by' => 'Evolution Marketing FLORIDA',
            'phone' => "585-981-8463",
            'email' => "info@evolutionsouthflorida.com",
            'logo' => "evolution_marketing_florida.logo"
        ],
        Proposal::TEMPLATE_TYPE_VENICE_ONWARD => [
            'prepared_by' => 'Venice Onward',
            'phone' => "585-981-8463",
            'email' => "sam@veniceonward.com",
            'logo' => "venice_onward.logo"  
        ],
        Proposal::TEMPLATE_TYPE_LIQUOR_CMS => [
            'prepared_by' => 'Liquor CMS',
            'phone' => "585-981-8463",
            'email' => "sam@liquorcms.com",
            'logo' => "liquor_cms.logo"  
        ],
        Proposal::TEMPLATE_TYPE_CMS_MAX => [
            'prepared_by' => 'CMS MAX',
            'phone' => "585-981-8463",
            'email' => "sam@cmsmax.com",
            'logo' => "cms_max.logo"  
        ],
    ];

    public static function getTemplateContent($templateType)
    {
        $templateContent = self::TEMPLATE_DATA[$templateType];
        $templateContent['logo'] = file_get_contents(base_path() . "/resources/logos/" . $templateContent['logo']);

        return $templateContent;
    }
}
