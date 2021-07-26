<?php

/*
 * This file is part of the NFQ package.
 *
 * (c) Nfq Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace NFQ\SyliusOmnisendPlugin\Client\Response\Model;

use JMS\Serializer\Annotation as Serializer;

class OrderSuccess
{
    /**
     * @var string|null
     * @Serializer\Type("string")
     */
    private $email;

    /**
     * @var string|null
     * @Serializer\Type("string")
     */
    private $phone;

    /**
     * @var string|null
     * @Serializer\Type("string")
     */
    private $orderID;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getOrderID(): ?string
    {
        return $this->orderID;
    }

    public function setOrderID(?string $orderID): self
    {
        $this->orderID = $orderID;

        return $this;
    }
}
