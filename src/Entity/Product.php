<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\SluggerInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @UniqueEntity("slug")
 * 
 * @ApiResource(
 *     collectionOperations={"get"={"normalization_context"={"groups"="product:list"}}},
 *     itemOperations={"get"={"normalization_context"={"groups"="product:item"}}},
 *     order={"price"="ASC"},
 *     paginationEnabled=false
 * )
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['product:list', 'product:item'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['product:list', 'product:item'])]
    private $Name;

    /**
     * @ORM\Column(type="float")
     */
    #[Groups(['product:list', 'product:item'])]
    private $price;

    /**
     * @ORM\Column(type="boolean")
     */
    #[Groups(['product:list', 'product:item'])]
    private $freeShipment;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="product", orphanRemoval=true)
     */
    private $reviews;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['product:list', 'product:item'])]
    private $brand;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    #[Groups(['product:list', 'product:item'])]
    private $slug;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function __toString(): string
    {
            return $this->Name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getFreeShipment(): ?bool
    {
        return $this->freeShipment;
    }

    public function setFreeShipment(bool $freeShipment): self
    {
        $this->freeShipment = $freeShipment;

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setProduct($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getProduct() === $this) {
                $review->setProduct(null);
            }
        }

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

      public function computeSlug(SluggerInterface $slugger)
    {
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = (string) $slugger->slug((string) $this)->lower();
        }
    }
}
