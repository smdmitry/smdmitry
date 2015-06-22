<?php

namespace Autosms\Service;

class Command
{
    public static function i() {static $i; $i = new static(); return $i;}

    const TYPE_BALANCE = 1;
    const TYPE_INFO = 2;
    const TYPE_COORDS = 3;
    const TYPE_COORDS_SHORT = 33;
    const TYPE_SEARCH = 5;
    const TYPE_ENGINE_ON = 11;
    const TYPE_ENGINE_OFF = 12;
    const TYPE_HEATING_SWITCH = 13;
    const TYPE_OPEN = 7;
    const TYPE_CLOSE = 6;
    const TYPE_OPEN_PARTLY = 77;

    const STATUS_NEW = 0;
    const STATUS_ERROR_SENDING_SMS = 101;
    const STATUS_FINISHED = 1;
    const STATUS_SENT_SMS = 2;
    const STATUS_UNEXPECTED_ANSWER = 103;
    const STATUS_WRONG_CODE = 104;
    const STATUS_EXPIRED = 102;

    public function getTypes()
    {
        return array(
            self::TYPE_BALANCE => array('title' => 'Баланс'),
            self::TYPE_INFO =>  array('title' => 'Информация'),
            self::TYPE_COORDS =>  array('title' => 'Координаты'),
            self::TYPE_SEARCH =>  array('title' => 'Поиск на парковке'),
            self::TYPE_ENGINE_ON =>  array('title' => 'Завести двигатель'),
            self::TYPE_ENGINE_OFF =>  array('title' => 'Заглушить двигатель'),
            self::TYPE_OPEN =>  array('title' => 'Открыть'),
            self::TYPE_OPEN_PARTLY =>  array('title' => 'Открыть (частично)'),
            self::TYPE_CLOSE =>  array('title' => 'Закрыть'),
            self::TYPE_HEATING_SWITCH =>  array('title' => 'Обогрев'),
        );
    }

    public function getModel($user, $car, $cmd, $param = 0)
    {
        $config = $this->getConfigFromType($cmd, $param);
        if (empty($config)) {
            return false;
        }

        $data = array(
            'user_id' => $user->getId(),
            'car_id' => $car->getId(),
            'type' => $cmd,
            'status' => self::STATUS_NEW,
            'inserted' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s'),
        );


        $model = new \Autosms\Model\Command($data);

        return $model;
    }

    public function getConfigFromType($type)
    {
        $result = array();

        if ($type == \Autosms\Service\Command::TYPE_BALANCE) {
            $text = '01#';
        } else if ($type == \Autosms\Service\Command::TYPE_INFO) {
            $text = '02#';
        } else if ($type == \Autosms\Service\Command::TYPE_COORDS) {
            $text = '03*';
        } else if ($type == \Autosms\Service\Command::TYPE_COORDS_SHORT) {
            $text = '03#';
        } else if ($type == \Autosms\Service\Command::TYPE_SEARCH) {
            $text = '05#';
        } else if ($type == \Autosms\Service\Command::TYPE_OPEN) {
            $text = '07#';
            $needCode = true;
        } else if ($type == \Autosms\Service\Command::TYPE_OPEN_PARTLY) {
            $text = '07*';
        } else if ($type == \Autosms\Service\Command::TYPE_CLOSE) {
            $text = '06#';
        } else if ($type == \Autosms\Service\Command::TYPE_ENGINE_ON) {
            $text = '11#';
        } else if ($type == \Autosms\Service\Command::TYPE_ENGINE_OFF) {
            $text = '12#';
        } else if ($type == \Autosms\Service\Command::TYPE_HEATING_SWITCH) {
            $text = '13#';
        }

        if (empty($text)) {
            return false;
        }

        $result['text'] = $text;
        if (!empty($needCode)) {
            $result['need_code'] = $needCode;
        }

        return $result;
    }

    public function received($command, $text)
    {
        $status = \Autosms\Service\Command::STATUS_UNEXPECTED_ANSWER;
        $data = $text;

        $pos = strpos($text, 'Неверный код');
        if ($pos !== false) {
            $status = \Autosms\Service\Command::STATUS_WRONG_CODE;
        } else {
            if ($command->getType() == \Autosms\Service\Command::TYPE_BALANCE) {
                $pos = strpos($text, 'Баланс лицевого счета');
                $status = \Autosms\Service\Command::STATUS_UNEXPECTED_ANSWER;

                if ($pos !== false) {
                    $status = \Autosms\Service\Command::STATUS_FINISHED;
                }
            } else if ($command->getType() == \Autosms\Service\Command::TYPE_INFO) {
                $pos = strpos($text, 'Режим охраны');

                if ($pos !== false) {
                    $status = \Autosms\Service\Command::STATUS_FINISHED;
                }
            } else if ($command->getType() == \Autosms\Service\Command::TYPE_COORDS || $command->getType() == \Autosms\Service\Command::TYPE_COORDS_SHORT) {
                $pos = strpos($text, 'MCC');
                $pos2 = strpos($text, 'CID');

                if ($pos !== false && $pos2 !== false) {
                    $status = \Autosms\Service\Command::STATUS_FINISHED;
                }
            } else if (
                $command->getType() == \Autosms\Service\Command::TYPE_OPEN ||
                $command->getType() == \Autosms\Service\Command::TYPE_OPEN_PARTLY ||
                $command->getType() == \Autosms\Service\Command::TYPE_CLOSE ||
                $command->getType() == \Autosms\Service\Command::TYPE_SEARCH ||
                $command->getType() == \Autosms\Service\Command::TYPE_HEATING_SWITCH
            ) {
                $pos = strpos($text, 'Команда выполнена');

                if ($pos !== false) {
                    $status = \Autosms\Service\Command::STATUS_FINISHED;
                }
            } else if ($command->getType() == \Autosms\Service\Command::TYPE_ENGINE_ON) {
                $pos = strpos($text, 'Команда выполнена');
                $pos2 = strpos($text, 'Двигатель заведен');

                if ($pos !== false || $pos2 !== false) {
                    $status = \Autosms\Service\Command::STATUS_FINISHED;
                }
            } else if ($command->getType() == \Autosms\Service\Command::TYPE_ENGINE_OFF) {
                $pos = strpos($text, 'Команда выполнена');
                $pos2 = strpos($text, 'Двигатель заглушен');

                if ($pos !== false || $pos2 !== false) {
                    $status = \Autosms\Service\Command::STATUS_FINISHED;
                }
            }
        }

        //\Autosms\Dao\SMS::i()->update($command, $text, $status);
        $res = \Autosms\Dao\Command::i()->updateStatus($command->getId(), $status, $data);

        return $res;
    }

    public function getCoordsLink($text)
    {
        $text = '';
    }
}