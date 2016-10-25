<?php

namespace Logging\LoggingServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Description of LoggingServiceProvider
 *
 * @author paul
 */
class LoggingServiceProvider implements ServiceProviderInterface
{

    public function boot(Application $app)
    {
        if( $app->offsetExists('logger.directory') )
        {
            $app['logger.factory']->set('dir_log', $app['logger.directory']);
        }
        else
        {
            $app['logger.factory']->set('dir_log', './');
        }

        $app['logger.factory']->configure($app['ongoo.loggers']);
        $root = $app['logger.factory']->get('root');
        $app['logger'] = $root;
    }

    public function register(Application $app)
    {
        $app['logger.factory'] = $app->share(function() use(&$app)
                {
                    $factory = \Logging\LoggersManager::getInstance();
                    if ($app->offsetExists('logger.class'))
                    {
                        $factory->setLoggerClass($app['logger.class']);
                    }
                    return $factory;
                });

        if( !$app->offsetExists('logger.exception_handler'))
        {
            $app['logger.exception_handler'] = $app->protect(function (\Exception $e, $code) use(&$app)
                {
                    $app['logger']->error("Error catcher has catch:");
                    $app['logger']->error($e);
                });
        }
        
        $app->error(function (\Exception $e, $code) use(&$app){
            $app['logger.exception_handler']($e, $code);
        });

        $app['logger.interpolate'] = $app->protect(function($message, $context = array()) use(&$app) {
            return $app['logger.factory']->interpolate($message, $context);
        });
        
        $app['logger.flattern'] = $app->protect(function($item, $level) use(&$app) {
            return $app['logger.factory']->flattern($item, $level);
        });
        
        $app['logger.prettydump'] = $app->protect(function($variable, $context = array()) use(&$app) {
            return $app['logger.factory']->prettydump($variable, $context);
        });
    }

}

if( class_exists('\Symfony\Component\HttpKernel\Log\LoggerInterface') )
{
    class Logger extends \Logging\Logger implements \Symfony\Component\HttpKernel\Log\LoggerInterface
    {

        public function crit($message, array $context = array())
        {
            $context['debug_backtrace'] = debug_backtrace();
            $this->critical($message, $context);
        }

        public function emerg($message, array $context = array())
        {
            $context['debug_backtrace'] = debug_backtrace();
            $this->emergency($message, $context);
        }

        public function err($message, array $context = array())
        {
            $context['debug_backtrace'] = debug_backtrace();
            $this->error($message, $context);
        }

        public function warn($message, array $context = array())
        {
            $context['debug_backtrace'] = debug_backtrace();
            $this->warning($message, $context);
        }

    }
}
