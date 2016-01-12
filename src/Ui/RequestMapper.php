<?php

namespace Phperf\Xhprof\Ui;

use Yaoi\BaseClass;
use Yaoi\Command;
use Yaoi\Command\Option;
use Yaoi\Io\Request;
use Yaoi\String\Utils;

class RequestMapper extends BaseClass
{
    public $values = array();


    /**
     * @param Request $request
     * @param array|Option[] $options
     * @return $this
     * @throws Command\Exception
     */
    public function read(Request $request, array $options)
    {
        foreach ($options as $option) {
            $publicName = $this->getPublicName($option->name);
            if (false !== ($value = $request->request($publicName, false)
                )
            ) {
                if (Option::TYPE_ENUM === $option->type && !isset($option->values[$value])) {
                    throw new Command\Exception('Invalid value for ' . $publicName, Command\Exception::INVALID_VALUE);
                }
                if (!$value && Option::TYPE_VALUE === $option->type) {
                    throw new Command\Exception('Value required for ' . $publicName, Command\Exception::VALUE_REQUIRED);
                }

                if ($option->isVariadic) {
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                }

                if (Option::TYPE_BOOL === $option->type) {
                    $value = (bool)$value;
                }

                $this->values[$option->name] = $value;
            }
            else {
                if ($option->isRequired) {
                    throw new Command\Exception('Option '. $publicName .' required', Command\Exception::OPTION_REQUIRED);
                }
            }
        }

        return $this;
    }


    public static function getPublicName($name)
    {
        return Utils::fromCamelCase($name, '_');
    }
}