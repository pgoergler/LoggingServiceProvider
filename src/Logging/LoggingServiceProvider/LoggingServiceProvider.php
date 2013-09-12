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

        $app->error(function (\Exception $e, $code) use(&$app)
                {
                    switch ($code)
                    {
                        case 404:
                            $message = 'The requested page could not be found.';
                            break;
                        default:
                            $message = 'We are sorry, but something went terribly wrong.';
                    }

                    $app['logger']->error("Error catcher has catch:");
                    $app['logger']->error($e);
                });
    }

}

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