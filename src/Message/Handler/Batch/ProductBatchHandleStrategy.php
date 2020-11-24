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

namespace NFQ\SyliusOmnisendPlugin\Message\Handler\Batch;

use Doctrine\ORM\EntityManagerInterface;
use NFQ\SyliusOmnisendPlugin\Builder\Request\ProductBuilderDirectorInterface;
use NFQ\SyliusOmnisendPlugin\Client\OmnisendClient;
use NFQ\SyliusOmnisendPlugin\Client\Request\Model\Batch;
use NFQ\SyliusOmnisendPlugin\Doctrine\ORM\ProductRepositoryInterface;
use NFQ\SyliusOmnisendPlugin\Factory\Request\BatchFactoryInterface;
use NFQ\SyliusOmnisendPlugin\Message\Command\CreateBatch;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use DateTime;

class ProductBatchHandleStrategy implements BatchHandlerStrategyInterface
{
    /** @var OmnisendClient */
    private $omnisendClient;

    /** @var ProductBuilderDirectorInterface */
    private $productBuilderDirectory;

    /** @var BatchFactoryInterface */
    private $batchFactory;

    /** @var ProductRepositoryInterface */
    private $repository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    public function __construct(
        OmnisendClient $omnisendClient,
        ProductBuilderDirectorInterface $productBuilderDirectory,
        BatchFactoryInterface $batchFactory,
        ProductRepositoryInterface $repository,
        EntityManagerInterface $entityManager,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->omnisendClient = $omnisendClient;
        $this->productBuilderDirectory = $productBuilderDirectory;
        $this->batchFactory = $batchFactory;
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->channelRepository = $channelRepository;
    }

    public function support(CreateBatch $batch): bool
    {
        return $batch->getType() === 'products';
    }

    public function handle(CreateBatch $message): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelRepository->findOneBy(['code' => $message->getChannelCode()]);
        $this->updateProducts($channel, $message);
        $this->createProducts($channel, $message);
    }

    private function createProducts(ChannelInterface $channel, CreateBatch $message): void
    {
        $rawData = $this->repository->findNotSyncedToOmnisend($channel);
        $this->pushData($rawData, $message, $channel, Batch::METHODS_POST);
    }

    private function updateProducts(ChannelInterface $channel, CreateBatch $message): void
    {
        $rawData = $this->repository->findSyncedToOmnisend($channel);
        $this->pushData($rawData, $message, $channel, Batch::METHODS_POST);
    }

    public function pushData(iterable $rawData, CreateBatch $message, ChannelInterface $channel, string $method): void
    {
        $resources = [];
        $iteration = 1;

        foreach ($rawData as $row) {
            $item = $row[0];
            $resources[] = $this->productBuilderDirectory->build($item, $channel, $message->getLocaleCode());;
            $item->setPushedToOmnisend(new DateTime());
            $this->entityManager->persist($item);

            if (($iteration % $message->getBatchSize()) === 0) {
                $this->postData($resources, $message, $method);
                $resources = [];
            }
            $iteration++;
        }

        if (count($resources) !== 0) {
            $this->postData($resources, $message, $method);
        }
    }

    private function postData(array $resources, CreateBatch $message, string $method): void
    {
        $response = $this->omnisendClient->postBatch(
            $this->batchFactory->create(
                $method,
                Batch::ENDPOINTS_PRODUCT,
                $resources
            ),
            $message->getChannelCode()
        );

        if (null !== $response) {
            $this->entityManager->flush();
        }
    }
}
