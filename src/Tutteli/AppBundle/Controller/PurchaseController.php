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
use Tutteli\AppBundle\Entity\Purchase;
use Tutteli\AppBundle\Entity\PurchasePosition;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PurchaseController extends AEntityController {
    
    protected function getCsrfTokenDomain() {
        return 'purchase';
    }
    
    protected function getSingularEntityName() {
        return 'purchase';
    }
    
    protected function getPluralEntityName() {
        return 'purchases';
    }
    
    protected function getRepository() {
        return $this->getDoctrine()->getRepository('TutteliAppBundle:Purchase');
    }
    
    protected function getJson($purchase) {
        /*@var $purchase \Tutteli\AppBundle\Entity\Purchase */
        return  '{'
                .'"id": "'.$purchase->getId().'"'
                .',"purchaseDate": "'.$this->getFormattedDate($purchase->getPurchaseDate()).'"'
                .',"total": "'.$purchase->getTotal().'"'
                .',"user": "'.$purchase->getUser()->getUsername().'"'
                .',"positions":'.$this->getJsonArray($purchase->getPositions(), [$this, 'getJsonForPosition'])
                .$this->getMetaJsonRows($purchase)
                .'}';
    }
    
    protected function getJsonForPosition(PurchasePosition $position) {
        return '{'
                .'"id":"'.$position->getId().'"'
                .',"expression":"'.$position->getExpression().'"'
                .',"price":"'.$position->getPrice().'"'
                .',"category":"'.$position->getCategory()->getName().'"'
                .',"notice":"'.$position->getNotice().'"'
                .$this->getMetaJsonRows($position)
                .'}';
    }
    
    public function monthAction() {
        return new RedirectResponse($this->container->get('router')->generate(
                'purchases_monthAndYear', 
                ['month' => date('m'), 'year' => date('Y')],
                UrlGeneratorInterface::ABSOLUTE_URL));
    }
    
    public function monthTplAction(Request $request) {
        return $this->getHtmlForEntities($request, '.tpl', 'month', null);
    }
    
    public function monthAndYearJsonAction(Request $request, $month, $year) {
        /*@var $repository \Tutteli\AppBundle\Entity\PurchaseRepository */
        $repository = $this->getRepository();
        return $this->getJsonForEntities($request, 
                function() use($repository, $month, $year) {
                    return $repository->getLastUpdatedForMonthOfYear($month, $year);
                }, 
                function() use($repository, $month, $year) {
                    return $repository->getForMonthOfYear($month, $year);
                });
    }
    
    public function monthAndYearAction(Request $request, $month, $year, $ending) {
        return $this->getHtmlForEntities($request, $ending, 'month', function() use($month, $year) {
            $repository = $this->getRepository();
            return $repository->getForMonthOfYear($month, $year);
        });
    }
  
    public function newAction(Request $request, $ending) {
        return parent::newAction($request, $ending);
    }
    
    public function postAction(Request $request) {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            return $this->savePurchase($request);
        }
        return new Response('{"msg": "Wrong Content-Type"}', Response::HTTP_BAD_REQUEST);
    }
    
    private function savePurchase(Request $request) {
        list($data, $response) = $this->decodeDataAndVerifyCsrf($request);
        if (!$response) {
            $purchase = new Purchase();
            $errors = $this->mapPurchase($purchase, $data);
            $validator = $this->get('validator');
            $errorsPurchase = $validator->validate($purchase);
            $errorsPurchase = $this->translateErrors($errorsPurchase, null);
            $errors->addAll($errorsPurchase);
            if (count($errors) > 0) {
                $response = $this->getValidationResponse($errors);
            } else {
                $em = $this->getDoctrine()->getManager();
                $em->persist($purchase);
                foreach($purchase->getPositions() as $position) {
                    $em->persist($position);
                }
                $em->flush();
                $response = $this->getCreateResponse($purchase->getId());
            }
        }
        return $response;
        
    }
    
    private function mapPurchase($purchase, $data) {
        $em = $this->getDoctrine()->getManager();
        $purchase->setUser($em->getReference('TutteliAppBundle:User', $data['userId']));
        $purchase->setPurchaseDate(new \DateTime($data['dt']));
    
        $validator = $this->get('validator');
        $errors = new ConstraintViolationList();
        $numPos = count($data['positions']);
        $total = 0;
        for ($i = 0; $i < $numPos; ++$i) {
            $dataPos = $data['positions'][$i];
            $position = $this->createPosition($dataPos);
            $newErrors = $validator->validate($position);
            if (count($newErrors) == 0) {
                $price = null;
                eval('$price = '.$dataPos['expression'].';');
                $position->setPrice($price);
                if ($price <= 0) {
                    $newErrors->add(new ConstraintViolation(
                        'purchase.price', 'purchase.price', [], $position, 'price', $price));      
                }
                $total += $price;
            }
            if(count($newErrors) > 0) {
                $newErrors = $this->translateErrors($newErrors, $i + 1);
                $errors->addAll($newErrors);
            }
            $position->setPurchase($purchase);
            $purchase->addPosition($position);
        }
        $purchase->setTotal($total);
        
        if ($numPos == 0) {
            $translator = $this->get('translator');
            $message = $translator->trans('purchase.positions', [], 'validators');
            $errors->add(new ConstraintViolation($message, $message, [], $purchase, 'positions', null)); 
        }
        
        return $errors;
    }
    
    private function createPosition(array $dataPos) {
        $position = new PurchasePosition();
        $em = $this->getDoctrine()->getManager();
        $position->setCategory($em->getReference('TutteliAppBundle:Category', $dataPos['categoryId']));
        $position->setExpression($dataPos['expression']);
        $position->setNotice($dataPos['notice']);
        return $position;
    }
    
    private function translateErrors(ConstraintViolationList $errorList, $posNumber){
        $list = new ConstraintViolationList();
        $translator = $this->get('translator');
        foreach ($errorList as $constraint) {
            $message = $translator->trans($constraint->getMessage(), ["pos" => $posNumber], 'validators');
            $list->add(new ConstraintViolation(
                    $message, 
                    $constraint->getMessageTemplate(), 
                    $constraint->getMessageParameters(),
                    $constraint->getRoot(),
                    $constraint->getPropertyPath(),
                    $constraint->getInvalidValue()));
        }
        return $list;
    }
    
    public function editAction(Request $request, $purchaseId, $ending) {
        return $this->edit($request, $ending, function() use ($purchaseId) {
            $category = $this->loadEntity($purchaseId);
            if ($category == null) {
                throw $this->createNotFoundException('Purchase with id '.$purchaseId. ' not found.');
            }
            return $category;
        });
    }
    
    public function editTplAction(Request $request) {
        return $this->edit($request, '.tpl', function(){ return null; });
    }
    
    private function edit(Request $request, $ending, callable $getPurchase) {
        $viewPath = '@TutteliAppBundle/Resources/views/Purchase/edit.html.twig';
        list($etag, $response) = $this->checkEndingAndEtagForView($request, $ending, $viewPath);
    
        if (!$response) {
            $purchase = $getPurchase();
            $response = $this->render($viewPath, array (
                    'notXhr' => $ending == '',
                    'error' => null,
                    'purchase' => $purchase,
            ));
    
            if ($ending == '.tpl') {
                $response->setETag($etag);
            }
        }
        return $response;
    }
    
}
