<?php
namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Flex\Types\Pages\PageObject;
use RocketTheme\Toolbox\Event\Event;
use Grav\Common\Utils;
use Grav\Plugin\AffiliateLinks\AffiliateLinks;


/**
 * Class AffiliateLinksPlugin
 * @package Grav\Plugin
 */
class AffiliateLinksPlugin extends Plugin
{

    /**
     * Gives the core a list of events the plugin wants to listen to
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0]
        ];
    }

    /**
     * Add template directory to twig lookup path.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {

        spl_autoload_register(function ($class) {
            if (Utils::startsWith($class, 'Grav\Plugin\AffiliateLinks\\')) {
                require_once __DIR__ .'/classes/' . strtolower(basename(str_replace("\\", '/', $class))) . '.php';
            }
        });

        // Admin only events
        if ($this->isAdmin()) {
            $this->enable([
                'onGetPageBlueprints' => ['onGetPageBlueprints', 0],
                'onAdminSave' => ['onAdminSave', 0],
            ]);
            return;
        }

        // Frontend events
        $this->enable([
            'onPageInitialized' => ['onPageInitialized', 0]
        ]);
    }

    /**
     * Extend page blueprints with additional configuration options.
     *
     * @param Event $event
     */
    public function onGetPageBlueprints($event)
    {
      $types = $event->types;
      $types->scanBlueprints('plugins://' . $this->name . '/blueprints');
    }


    public static function allowedProviders()
    {
       $grav = Grav::instance();
       $conf = $grav['config']->get('plugins.affiliate-links');//.output-amazon');
       $providers = [];
       foreach ($conf as $key => $value) {
           $vals = preg_split("/-/", $key);
           if (count($vals) == 2 && $vals[0] == "output" && $value) {
              $providers[$vals[1]] = ucfirst($vals[1]);
           }
       }
       return $providers;
    }

    public function onAdminSave(Event $event)
    {

        if (!$event['object'] instanceof PageObject) {
            return;
        }

        $page = $event['object'];
        $al = new AffiliateLinks($page);

    }

    /**
     * Insert meta tags and structured data to head of each page
     *
     * @param Event $e
     */
    public function onPageInitialized()
    {
       $page = $this->grav['page'];
       $al = new AffiliateLinks($page);

       if ($al->enabled)
         $this->grav['page']->header()->affiliation = $al->links;
    }

}
