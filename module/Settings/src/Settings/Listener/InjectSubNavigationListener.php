<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2104 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** InjectSubNavigationConfigListener.php */ 
namespace Settings\Listener;

use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\MvcEvent;

class InjectSubNavigationListener
{
    
    public function __invoke(MvcEvent $event)
    {
        if ($event->getViewModel()->terminate()) {
            /*
             * No need for navigation when it is not rendered anyway.
             */
            return;
        }
        
        $services     = $event->getApplication()->getServiceManager();
        $navigation   = $services->get('Core/Navigation');
        $settingsMenu = $navigation->findOneBy('route', 'lang/settings');
        
        if ($settingsMenu->hasChildren()) {
            /*
             * We already have the subnavigation.
             */
            return;
        }
        
        $moduleManager = $services->get('ModuleManager');
        $configPlugin  = $services->get('ControllerPluginManager')->get('config');
        
        $modules             = $moduleManager->getLoadedModules();
        $modulesWithSettings = $configPlugin("settings", array_keys($modules));
        
        $routeMatch   = $event->getRouteMatch();
        $router       = $event->getRouter();
        $activeModule = $event->getParam('__settings_active_module', false);
        $settingsMenu->setActive((bool) $activeModule);

        foreach($modulesWithSettings as $key => $param) {
            $page = array(
                'label' => isset($param['navigation_label']) ? $param['navigation_label'] : ucfirst($key),
                'order' => isset($param['navigation_order']) ? $param['navigation_order'] : '10',
                'class' => isset($param['navigation_class']) ? $param['navigation_class'] : null,
                'resource' => 'route/lang/settings',
                'route' => 'lang/settings',
                'router' => $router,
                'action' => 'index',
                'controller' => 'index',
                'params' => array('lang' => 'de', 'module' => $key),
                'active' => $key == $activeModule,
            );
            if ($routeMatch instanceof RouteMatch) {
                $page['routeMatch'] = $routeMatch;
            }
            $settingsMenu->addPage($page);
        }
        
    }
}

