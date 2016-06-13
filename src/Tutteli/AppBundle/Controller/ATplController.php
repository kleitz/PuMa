<?php
/*
 * This file is part of the project tutteli/puma published under the Apache License 2.0
 * For the full copyright and license information, please have a look at LICENSE in the
 * root folder or visit https://github.com/robstoll/PuMa
 */
namespace Tutteli\AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class ATplController extends Controller {
    
    protected abstract function getCsrfTokenDomain();
    
    public function csrfTokenAction() {
        $csrf = $this->get('security.csrf.token_manager');
        return new Response('{"csrf_token": "'.$csrf->getToken($this->getCsrfTokenDomain()).'"}');
    }
    
    protected function decodeDataAndVerifyCsrf(Request $request) {
        $response = null;
        $data = json_decode($request->getContent(), true);
        if ($data != null) {
            if (!array_key_exists('csrf_token', $data)
                    || !$this->isCsrfTokenValid($this->getCsrfTokenDomain(), $data['csrf_token'])) {
                        $response = new JsonResponse('Invalid CSRF token.', Response::HTTP_UNAUTHORIZED);
                    }
        } else {
            $response = new JsonResponse('No data provided.', Response::HTTP_BAD_REQUEST);
        }
        return [$data, $response];
    }
    
    protected function checkEndingAndEtagForView(Request $request, $ending, $viewPath) {
        return $this->checkEndingAndEtag($request, $ending, function() use ($viewPath){
            $path = $this->get('kernel')->locateResource($viewPath);
            $etag = hash_file('md5', $path);
            return $etag;
        });
    }
    
    protected function checkEndingAndEtag(Request $request, $ending, callable $callable) {
        if ($ending != '' && $ending != '.tpl') {
            throw $this->createNotFoundException('File Not Found');
        }
    
        $etag = '';
        $response = null;
        if ($ending == '.tpl') {
            if ($request->isXmlHttpRequest() || $this->container->get('kernel')->getEnvironment() == 'dev') {
                $response = new Response();
                $etag = $callable();
                $response->setETag($etag);
                if (!$response->isNotModified($request)) {
                    //is newer, need to generate a new response and cannot use the old one
                    $response = null;
                }
            } else {
                $response = new Response('partials are only available per xmlHttpRequest', Response::HTTP_BAD_REQUEST);
            }
        }
        return [$etag, $response];
    }
    
   
}