<?php
/**
 * @Author: binghe
 * @Date:   2017-06-19 11:33:38
 * @Last Modified by:   binghe
 * @Last Modified time: 2017-06-19 11:34:15
 */
namespace Binghe\Wechat\Staff;

use Binghe\Wechat\Core\AbstractAPI;

/**
 * Class Session.
 */
class Session extends AbstractAPI
{
    const API_CREATE = 'https://api.weixin.qq.com/customservice/kfsession/create';
    const API_CLOSE = 'https://api.weixin.qq.com/customservice/kfsession/close';
    const API_GET = 'https://api.weixin.qq.com/customservice/kfsession/getsession';
    const API_LISTS = 'https://api.weixin.qq.com/customservice/kfsession/getsessionlist';
    const API_WAITERS = 'https://api.weixin.qq.com/customservice/kfsession/getwaitcase';

    /**
     * List all sessions of $account.
     *
     * @param string $account
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function lists($account)
    {
        return $this->parseJSON('get', [self::API_LISTS, ['kf_account' => $account]]);
    }

    /**
     * List all waiters of $account.
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function waiters()
    {
        return $this->parseJSON('get', [self::API_WAITERS]);
    }

    /**
     * Create a session.
     *
     * @param string $account
     * @param string $openId
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function create($account, $openId)
    {
        $params = [
                   'kf_account' => $account,
                   'openid' => $openId,
                  ];

        return $this->parseJSON('json', [self::API_CREATE, $params]);
    }

    /**
     * Close a session.
     *
     * @param string $account
     * @param string $openId
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function close($account, $openId)
    {
        $params = [
                   'kf_account' => $account,
                   'openid' => $openId,
                  ];

        return $this->parseJSON('json', [self::API_CLOSE, $params]);
    }

    /**
     * Get a session.
     *
     * @param string $openId
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function get($openId)
    {
        return $this->parseJSON('get', [self::API_GET, ['openid' => $openId]]);
    }
}