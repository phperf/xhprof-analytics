<?php

namespace Phperf\Xhprof\Service;

use Yaoi\Database\Exception;

class ProfilingClient
{
    public static function addTag($name, $value = null) {
        if (self::$started) {
            self::$started->tags[$name] = $value;
        }
    }

    private static $projectName;
    public static function setProject($projectName)
    {
        self::$projectName = $projectName;
    }

    /** @var self */
    public static $started;

    public function cancel()
    {
        $this->saveCallback = null;
    }

    /** @var \Closure|null */
    private $saveCallback;

    private $tags = array();

    public static function stopOnShutdown()
    {
        if (self::$started) {
            self::$started->cancel();
        }

        self::$started = new self;

        self::$started->saveCallback = self::$started->getSaveCallback();
        $started = self::$started;

        register_shutdown_function(function() use ($started) {
            register_shutdown_function(function() use ($started) {
                if ($started->saveCallback) {
                    if (function_exists('fastcgi_finish_request')) {
                        fastcgi_finish_request();
                    }
                    $started->saveCallback->__invoke();
                }
            });
        });

    }


    public function stopAndSave($data)
    {
        $profileManager = new ProfileManager();
        $run = $profileManager->addRun($data);
        if (self::$projectName) {
            $run->setProjectByName(self::$projectName);
        }
        if ($this->tags) {
            $run->setTagsByValues($this->tags);
        }
        $profileManager->saveStats();
        $profileManager->addToAggregates($run);
        $run->delete();
    }

    private function getSaveCallback()
    {


        return function() {

            try {
                $data = array();
                if (extension_loaded('tideways')) {
                    $data = tideways_disable();
                } elseif (extension_loaded('xhprof')) {
                    $data = xhprof_disable();
                }

                if (extension_loaded('tideways')) {
                    tideways_enable(TIDEWAYS_FLAGS_CPU | TIDEWAYS_FLAGS_MEMORY);
                } elseif (extension_loaded('xhprof')) {
                    xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
                }

                $this->stopAndSave($data);

                $this->tags = array('saving' => null/*, 'simple_cast' => null*/);

                $data = array();
                if (extension_loaded('tideways')) {
                    $data = tideways_disable();
                } elseif (extension_loaded('xhprof')) {
                    $data = xhprof_disable();
                }

                $this->stopAndSave($data);
            }
            catch (Exception $exception) {
                ini_set('xdebug.var_display_max_depth', 5);
                ini_set('xdebug.var_display_max_children', 256);
                ini_set('xdebug.var_display_max_data', 10240);

                var_dump($exception->getMessage(), $exception->query);
            }

        };
    }

}