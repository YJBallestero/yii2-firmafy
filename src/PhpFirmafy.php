<?php
/*
 *  @author    Y.J. Ballestero <yjballestero@gmail.com>
 *  @copyright Copyright (c) 2020-2023. YJ Ballestero
 *  @license   https://yjballestero.com/docs/software-license
 *  @link      https://yjballestero.com
 */

namespace yjballestero\firmafy;

use JsonException;

class PhpFirmafy
{
    private $_data;
    private $_target;
    private $_user;
    private $_password;
    private $_mkey;
    private $_token;

    /**
     * @throws JsonException
     */
    function __construct()
    {
        $this->_target = 'https://app.firmafy.com/ApplicationProgrammingInterface.php';
        $this->_user = 'USUARIO app.firmafy.com';
        $this->_password = 'CLAVE app.firmafy.com';
        $this->_mkey = 'client.mkey';
        $this->_token = $this->auth();
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * @return false|string|void|null
     * @throws JsonException
     */
    private function auth()
    {
        if ($this->_token === '') {
            return file_exists($this->_mkey) && $this->checkTimeKey() ? file_get_contents($this->_mkey) : $this->getToken();
        }
    }

    /**
     * @return null
     * @throws JsonException
     */
    private function getToken()
    {
        $this->_data = array(
            'action'   => 'login',
            'usuario'  => $this->_user,
            'password' => $this->_password
        );
        $token = $this->send();
        $token = json_decode($token, false, 512, JSON_THROW_ON_ERROR);
        if (!$token->error) {
            file_put_contents($this->_mkey, $token->data);
            return $token->data;
        }
        return null;
    }

    /**
     * @return bool
     */
    private function checkTimeKey(): bool
    {
        $now = time();
        if (is_file($this->_mkey)) {
            return $now - filemtime($this->_mkey) < 60 * 60 * 1;
        }
        return false;
    }

    /**
     * @return bool|string
     */
    public function send()
    {
        $target_url = $this->_target;
        $post = $this->_data;
        $post['token'] = $this->_token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
