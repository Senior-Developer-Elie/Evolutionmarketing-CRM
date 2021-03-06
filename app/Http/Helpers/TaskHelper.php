<?php
namespace App\Http\Helpers;

class TaskHelper {

    public static function getAllEmailHost()
    {
        return [
            "g-suite"       => "Google Workspace",
            "office-365"    => "Office 365",
            "other"         => "Other",
            "not-needed"    => "Not Needed"
        ];
    }

    public static function getAllPreLiveOptions()
    {
        return [
            "setup-client-billing"          => "Setup Client Billing",
            "add-client-folder-in-g-drive"  => "Add client folder in G Drive",
            "get-domain-register-info"      => "Get Domain Register Info",
            "get-email-info"                => "Get Email Info",
            "check-thank-you-pages"         => "Check thank you pages match correct forms",
            "check-forms-have-notify"       => "Check forms have notify email setup",
            "setup-business-information"    => "Setup business information page",
            "yext-scan"                     => "Yext Scan",
            "social-media-image"            => "Social Media Image",
            "favicon"                       => "Favicon",
            "affiliate-target-area-industry" => "Affiliate, Target Area & Industry",
            "define-social-urls"            => "Define Social URLs",
            //"cross-browser-testing"         => "Cross browser testing",
            "broken-link-scanner-done"      => "Broken link scanner done",
            "checked-301-redirects"         => "Checked 301 Redirects",
            "google-analytics-access"       => "Google Analytics Access",
            "setup-google-search-console"   => "Setup Google Search Console",
            // "upload-icon"                   => "Upload Icon",
            "setup-google-recaptcha"        => "Setup Google reCAPTCHA",
            "tap-clicks"                    => "TapClicks",
            "signed-up-for-cms-max"         => "Signed up for CMS Max",
        ];
    }
}
