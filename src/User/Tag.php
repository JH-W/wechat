<?php
/**
 * @Author: binghe
 * @Date:   2017-06-12 13:19:43
 * @Last Modified by:   binghe
 * @Last Modified time: 2017-06-12 13:20:47
 */
namespace Binghe\Wechat\User;

use Binghe\Wechat\Core\AbstractAPI;

/**
 * Class Tag.
 */
class Tag extends AbstractAPI
{
    const API_GET = 'https://api.weixin.qq.com/cgi-bin/tags/get';
    const API_CREATE = 'https://api.weixin.qq.com/cgi-bin/tags/create';
    const API_UPDATE = 'https://api.weixin.qq.com/cgi-bin/tags/update';
    const API_DELETE = 'https://api.weixin.qq.com/cgi-bin/tags/delete';
    const API_USER_TAGS = 'https://api.weixin.qq.com/cgi-bin/tags/getidlist';
    const API_MEMBER_BATCH_TAG = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging';
    const API_MEMBER_BATCH_UNTAG = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging';
    const API_USERS_OF_TAG = 'https://api.weixin.qq.com/cgi-bin/user/tag/get';

    /**
     * Create tag.
     *
     * @param string $name
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function create($name)
    {
        $params = [
                   'tag' => ['name' => $name],
                  ];

        return $this->parseJSON('json', [self::API_CREATE, $params]);
    }

    /**
     * List all tags.
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function lists()
    {
        return $this->parseJSON('get', [self::API_GET]);
    }

    /**
     * Update a tag name.
     *
     * @param int    $tagId
     * @param string $name
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function update($tagId, $name)
    {
        $params = [
                   'tag' => [
                               'id' => $tagId,
                               'name' => $name,
                              ],
                  ];

        return $this->parseJSON('json', [self::API_UPDATE, $params]);
    }

    /**
     * Delete tag.
     *
     * @param int $tagId
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function delete($tagId)
    {
        $params = [
                   'tag' => ['id' => $tagId],
                  ];

        return $this->parseJSON('json', [self::API_DELETE, $params]);
    }

    /**
     * Get user tags.
     *
     * @param string $openId
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function userTags($openId)
    {
        $params = ['openid' => $openId];

        return $this->parseJSON('json', [self::API_USER_TAGS, $params]);
    }

    /**
     * Get users from a tag.
     *
     * @param string $tagId
     * @param string $nextOpenId
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function usersOfTag($tagId, $nextOpenId = '')
    {
        $params = ['tagid' => $tagId, 'next_openid' => $nextOpenId];

        return $this->parseJSON('json', [self::API_USERS_OF_TAG, $params]);
    }

    /**
     * Batch tag users.
     *
     * @param array $openIds
     * @param int   $tagId
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function batchTagUsers(array $openIds, $tagId)
    {
        $params = [
                   'openid_list' => $openIds,
                   'tagid' => $tagId,
                  ];

        return $this->parseJSON('json', [self::API_MEMBER_BATCH_TAG, $params]);
    }

    /**
     * Untag users from a tag.
     *
     * @param array $openIds
     * @param int   $tagId
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function batchUntagUsers(array $openIds, $tagId)
    {
        $params = [
                   'openid_list' => $openIds,
                   'tagid' => $tagId,
                  ];

        return $this->parseJSON('json', [self::API_MEMBER_BATCH_UNTAG, $params]);
    }
}
