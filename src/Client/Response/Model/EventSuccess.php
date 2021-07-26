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

class EventSuccess
{
    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $eventID;

    public function getEventID(): string
    {
        return $this->eventID;
    }

    public function setEventID(string $eventID): void
    {
        $this->eventID = $eventID;
    }
}
