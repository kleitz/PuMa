<?php
/*
 * This file is part of the project tutteli-purchase published under the Apache License 2.0
 * For the full copyright and license information, please have a look at LICENSE in the
 * root folder or visit https://github.com/robstoll/purchase
 */


namespace Tutteli\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="purchase_position")
 * @ORM\Entity
 */
class PurchasePosition {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\Regex(pattern="#^[0-9]+(.[0-9]+)?(\s*(\+|-|\*)\s*[0-9]+(.[0-9]+)?)*$#", message="purchase.expr")
     */
    private $expression;
    
    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $price;
    
    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    private $notice;
    
    /**
     * @ORM\ManyToOne(targetEntity="Purchase", inversedBy="positions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $purchase;

    /**
     * @ORM\Column(type="date")
     */
    private $createdAt;
    
    /**
     * @ORM\Column(type="date")
     */
    private $updatedAt;
    
    /**
     * @ORM\PrePersist
     */
    public function doStuffOnPrePersist() {
        $this->createdAt = new \DateTime();
    }
    
    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate() {
        $this->updatedAt = new \DateTime();
    }
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return PurchasePosition
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set notice
     *
     * @param string $notice
     *
     * @return PurchasePosition
     */
    public function setNotice($notice)
    {
        $this->notice = $notice;

        return $this;
    }

    /**
     * Get notice
     *
     * @return string
     */
    public function getNotice()
    {
        return $this->notice;
    }

    /**
     * Set category
     *
     * @param \Tutteli\AppBundle\Entity\Category $category
     *
     * @return PurchasePosition
     */
    public function setCategory(\Tutteli\AppBundle\Entity\Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Tutteli\AppBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set purchase
     *
     * @param \Tutteli\AppBundle\Entity\Purchase $purchase
     *
     * @return PurchasePosition
     */
    public function setPurchase(\Tutteli\AppBundle\Entity\Purchase $purchase)
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * Get purchase
     *
     * @return \Tutteli\AppBundle\Entity\Purchase
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * Set expression
     *
     * @param string $expression
     *
     * @return PurchasePosition
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * Get expression
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PurchasePosition
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return PurchasePosition
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
