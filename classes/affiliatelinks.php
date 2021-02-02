<?php
namespace Grav\Plugin\AffiliateLinks;

use Grav\Common\Grav;
use Grav\Common\Helpers\Truncator;

class AffiliateLinks
{
    private $affiliateLinks;
    public  $links;
    public  $enabled;
    private $grav;

    private $otherPresence = array(
        'website-url',
    );

    /**
     * Initializes Aura variables for the page
     *
     * @param  object $page
     *
     */
    public function __construct($page)
    {

        $this->grav = $cache = Grav::instance();
        $this->links = [];
        $this->enabled = false;

	// Need to parse the given affiliation value
        $alnks = 'affiliate-links';
        if (!isset($page->header()->$alnks)) return;

        $affiliateLinks = (array)($page->header()->$alnks);
        $this->affiliateLinks = $affiliateLinks;
        $this->enabled = $affiliateLinks['enabled'] && isset($affiliateLinks['affiliation']);

        if (!$this->enabled) return;
        foreach ($affiliateLinks['affiliation'] as $links) {
            switch($links['link_type']) {
            case "amazon":      $this->parseAmazon($links['link_url'], $links['link_desc']); break;
            case "aliexpress":  $this->parseAliExpress($links['link_url'], $links['link_desc']); break;
            default:            $this->parseOther($links['link_url'], $links['link_desc']); break;
            }
        }
    }

    public function parseOther($url, $desc, $img = '', $provider = '') {
        $this->links[] = ['img' => $img, 'url' => $url, 'desc' => $desc, 'provider' => $provider];
    }
    public function parseCommon($url, $desc, $prov) {
        $doc = Truncator::htmlToDomDocument($url);
        $xml = simplexml_import_dom($doc);
        // We want to extract the first link we find
        $links = $xml->xpath('//a');
        if (count($links)) $url = $links[0]['href'];
        $imgs = $xml->xpath('//img');
        $img = '';
        if (count($imgs)) $img = $imgs[0]['src'];
        return $this->parseOther($url, $desc, $img, $prov);
    }
    public function parseAmazon($url, $desc) {
        return $this->parseCommon($url, $desc, 'Amazon');
    }
    public function parseAliExpress($url, $desc) {
        return $this->parseCommon($url, $desc, 'AliExpress');
    }
}
