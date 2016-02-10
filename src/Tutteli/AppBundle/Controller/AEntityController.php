<?php
/*
 * This file is part of the project tutteli/purchase published under the Apache License 2.0
 * For the full copyright and license information, please have a look at LICENSE in the
 * root folder or visit https://github.com/robstoll/purchase
 */
namespace Tutteli\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AEntityController extends Controller {
    
    protected abstract function getCsrfTokenDomain();
    protected abstract function getSingularEntityName();
    protected abstract function getPluralEntityName();
    /**
     * @return \Tutteli\AppBundle\Entity\ARepository
     */
    protected abstract function getRepository();
    protected abstract function getJson($entity);
    
    
    public function cgetJsonAction(Request $request) {
        $repository = $this->getRepository();
        $lastUpdatedEntity = $repository->getLastUpdated();
        $updatedAt = $lastUpdatedEntity->getUpdatedAt();
        $response = $this->checkUpdatedAt($request, $updatedAt);
        if ($response === null) {
            $data = $repository->findAll();
            $jsonArray = $this->getJsonArray($data);
            $response = new Response(
                    '{'
                    .'"'.$this->getPluralEntityName().'":'.$jsonArray.','
                    .'"updatedAt":"'.$this->getFormattedDateTime($updatedAt).'",'
                    .'"updatedBy":"'.$lastUpdatedEntity->getUpdatedBy()->getUsername().'"'
                    .'}');
            $response->setLastModified($updatedAt);
        }
        return $response;
    }
    
    private function getJsonArray(array $data) {
        $list = '';
        $count = count($data);
        if ($count > 0) {
            for ($i = 0; $i < $count; ++$i) {
                if ($i != 0) {
                    $list .= ',';
                }
                /* @var $user \Tutteli\AppBundle\Entity\User */
                $user = $data[$i];
                $list .= $this->getJson($user);
            }
        }
        return '['.$list.']';
    }
    

    protected function getJsonForEntityAction($entityId) {
        $entity = $this->loadEntity($entityId);
        return new Response('{"'.$this->getSingularEntityName().'":'.$this->getJson($entity).'}');
    }
    
    protected function loadEntity($id) {
        $repository = $this->getRepository();
        return $repository->find($id);
    }
    
    protected function loadEntities() {
        $repository = $this->getRepository();
        return $repository->findAll();
    }
    
    public function csrfTokenAction() {
        $csrf = $this->get('security.csrf.token_manager');
        return new Response('{"csrf_token": "'.$csrf->getToken($this->getCsrfTokenDomain()).'"}');
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
    
    protected function checkUpdatedAt(Request $request, \DateTime $updatedAt){
        $response = new Response();
        $response->setLastModified($updatedAt);
        if (!$response->isNotModified($request)) {
            //is newer, need to generate a new response and cannot use the old one
            $response = null;
        }
        return $response;
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
    
    protected function getTranslatedValidationResponse(ConstraintViolationList $errorList) {
        $translator = $this->get('translator');
        $errors = array();
        foreach ($errorList as $error) {
            $errors[$error->getPropertyPath()] = $translator->trans($error->getMessage(), [], 'validators');
        }
        return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
    }
    
    protected function getValidationResponse(ConstraintViolationList $errorList) {
        $errors = array();
        foreach ($errorList as $error) {
            $errors[$error->getPropertyPath()] = $error->getMessage();
        }
        return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
    }
    
    protected function getCreateResponse($id){
        return new Response('{"id": "'.$id.'"}', Response::HTTP_CREATED);
    }
    
    protected function getFormattedDateTime($date) {
        $format = $this->get('translator')->trans('general.dateTimeFormat.php');
        return $date->format($format);
    }
    
    protected function getMetaJsonRows($entity) {
        return ',"updatedAt":"'.$this->getFormattedDateTime($entity->getUpdatedAt()).'"'
              .',"updatedBy": "'.$entity->getUpdatedBy()->getUsername().'"';
    }
    
}