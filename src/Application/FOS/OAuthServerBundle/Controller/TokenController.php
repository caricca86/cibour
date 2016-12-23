<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\FOS\OAuthServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;

class TokenController extends \FOS\OAuthServerBundle\Controller\TokenController
{
    /**
     * @var OAuth2
     */
    protected $server;

    /**
     * @param OAuth2 $server
     */
    public function __construct(OAuth2 $server)
    {
        parent::__construct($server);
    }

    /**
     * @param  Request $request
     * @return type
     */
    public function tokenAction(Request $request)
    {
        try {
            $this->log($request);
            return $this->server->grantAccessToken($request);
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }

    private function log(Request $request)
    {
        $data_log = "\n======================".date('d-m-Y h:i:s');
        $data_log = $data_log."\n".$request->__toString()."\n";
        $data_log = $data_log."\nQuery Parameters\n".print_r($request->query->all(), true);
        $data_log = $data_log."\nRequest Parameters\n".print_r($request->request->all(), true);
        $data_log = $data_log."\nFiles\n".print_r($request->files->all(), true);
        file_put_contents('token.log', $data_log, FILE_APPEND);
    }

}
