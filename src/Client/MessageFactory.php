<?php

/*
 * @copyright C UAB NFQ Technologies
 *
 * This Software is the property of NFQ Technologies
 * and is protected by copyright law – it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * Contact UAB NFQ Technologies:
 * E-mail: info@nfq.lt
 * http://www.nfq.lt
 */

declare(strict_types=1);

namespace NFQ\SyliusOmnisendPlugin\Client;

use Http\Discovery\MessageFactoryDiscovery;
use NFQ\SyliusOmnisendPlugin\Model\ChannelOmnisendTrackingAwareInterface;
use Psr\Http\Message\RequestInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use \Http\Message\MessageFactory as BaseFactory;

class MessageFactory
{
    /** @var BaseFactory */
    private $factory;

    /** @var ChannelContextInterface */
    private $channelContext;

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(ChannelContextInterface $channelContext, SerializerInterface $serializer)
    {
        $this->channelContext = $channelContext;
        $this->serializer = $serializer;
    }

    private function getMessageFactory(): BaseFactory
    {
        if ($this->factory === null) {
            $this->factory = MessageFactoryDiscovery::find();
        }

        return $this->factory;
    }

    public function create(string $type, string $url, $data): RequestInterface
    {
        /** @var ChannelOmnisendTrackingAwareInterface $channel */
        $channel = $this->channelContext->getChannel();

        return $this->getMessageFactory()->createRequest(
            $type,
            $url,
            [
                'Content-Type' => 'application/json',
                'X-API-KEY' => '5c5af4578653ed7d78a067e5-EgZ7yp8GF0TV49JBSo7a0xRv2hjP2vmZkbTEi5xq327uK4pnxj'//TODO: $channel->getOmnisendTrackingKey()
            ],
            $data ?
                $this->serializer->serialize(
                    $data,
                    'json',
                    [
                        AbstractObjectNormalizer::SKIP_NULL_VALUES => true
                    ]
                ) :
                null
        );
    }
}
