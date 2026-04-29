<?php


namespace App\Serializer;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PaginatedCollectionNormalizer  implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'paginated_collection_normalizer_already_called';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (($context[self::ALREADY_CALLED] ?? false) === true) {
            return false;
        }

        return $data instanceof PaginatorInterface && $data instanceof \Traversable;
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!$data instanceof PaginatorInterface || !$data instanceof \Traversable) {
            throw new \LogicException('Expected a paginated traversable collection.');
        }

        $context[self::ALREADY_CALLED] = true;

        $items = [];
        foreach ($data as $item) {
            $items[] = $this->normalizer->normalize($item, $format, $context);
        }

        return [
            'items' => $items,
            'pagination' => [
                'page' => (int)$data->getCurrentPage(),
                'itemsPerPage' => (int)$data->getItemsPerPage(),
                'lastPage' => (int)$data->getLastPage(),
                'totalItems' => (int)$data->getTotalItems(),
            ],
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PaginatorInterface::class => true,
        ];
    }
}
